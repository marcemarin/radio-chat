<?php

namespace App\Classify;

use Marcemarin\TypeSafe\Contracts\Client as TypeSafe;
use Marcemarin\TypeSafe\Questions\Choice;
use Marcemarin\TypeSafe\Questions\Noul;
use Marcemarin\TypeSafe\Questions\Score;

/**
 * Clasificador híbrido.
 *
 * Las decisiones (intención, tono, puntaje para el aire, moderación) las toma Jev, el modelo de
 * decisiones tipadas de TypeSafe AI, a través de marcemarin/typesafe-laravel: rápido, barato y con
 * probabilidades calibradas. Lo que requiere generar o extraer texto (tema, lugar, nombre, resumen)
 * se delega a otro Classifier, y solo para los mensajes que valen la pena.
 */
class JevClassifier implements Classifier
{
    /** Por debajo de este puntaje (0-100) no se gasta en extracción con LLM. */
    private const EXTRACT_MIN_SCORE = 40;

    private const FLAG_AT = 0.6;

    private const REVIEW_FROM = 0.4;

    public function __construct(
        private readonly TypeSafe $typesafe,
        private readonly Classifier $extractor,
        private readonly bool $extractorIsPaid,
    ) {}

    public function classify(string $text, array $ctx = []): Classification
    {
        $t0 = hrtime(true);
        $result = $this->typesafe
            ->state(array_filter([
                'message' => $text,
                'is_voice_note_transcript' => (bool) ($ctx['is_audio'] ?? false),
                'whatsapp_name' => $ctx['contact_name'] ?? null,
                'program' => trim(($ctx['program'] ?? '').' '.($ctx['station'] ?? '')) ?: null,
                'program_context' => $ctx['context'] ?? null,
            ], fn ($v) => $v !== null))
            // Preguntas en inglés (el idioma más preciso del modelo) sobre un mensaje en español rioplatense.
            ->ask('intent', Choice::make("What is the listener's main purpose in `message`? It is a WhatsApp message in Argentine Spanish sent to a live radio show; it may be an automatic transcription of a voice note, with errors.")->options([
                'reclamo' => 'Complains about or reports a problem with a public service, street, institution or company',
                'pedido_musical' => 'Asks the show to play a song or artist',
                'saludo' => 'Only greets, thanks, congratulates or sends regards',
                'opinion' => 'Gives an opinion or comment about a topic being discussed',
                'concurso' => 'Participates in or asks about a contest, raffle or giveaway',
                'consulta' => 'Asks for information',
                'spam' => 'Advertising, chain messages, scams or content unrelated to the show',
                'otro' => 'None of the above, or unintelligible',
            ]))
            ->ask('sentiment', Choice::make('What is the emotional tone of `message`?')->options([
                'positivo' => 'Happy, grateful, enthusiastic',
                'neutral' => 'Neutral or purely informative',
                'negativo' => 'Angry, upset, worried or complaining',
            ]))
            ->ask('on_air', Score::make('How good is `message` to be read or played on air by a radio producer?')->levels([
                'Unusable: spam, insults, unintelligible or empty',
                'Weak: generic, no information, nothing the audience would care about',
                'Acceptable: clear but ordinary',
                'Good: clear, brief, relevant to listeners, the person says who they are or where they are from',
                "Excellent: clear, brief, emotional or newsworthy, with the listener's name and location",
            ]))
            ->ask('insulto', Noul::make('Does `message` contain insults, slurs or profanity directed at someone?'))
            ->ask('spam', Noul::make('Is `message` advertising, a scam or a chain message?'))
            ->ask('datos_personales', Noul::make('Does `message` contain a phone number, home address, ID number or other private personal data?'))
            ->get();
        $ms = (int) ((hrtime(true) - $t0) / 1e6);

        $intent = $result->choice('intent');
        $sentiment = $result->choice('sentiment');
        $onAir = $result->score('on_air');
        $score = (int) round($onAir->normalized() * 100);

        $flags = ['insulto', 'spam', 'datos_personales'];
        $moderation = array_values(array_filter($flags, fn ($f) => $result->noul($f)->isTrue(self::FLAG_AT)));
        $review = array_values(array_filter($flags, fn ($f) => $result->noul($f)->isUncertain(self::REVIEW_FROM, self::FLAG_AT)));
        if ($intent->is('spam') && ! in_array('spam', $moderation, true)) {
            $moderation[] = 'spam';
        }

        $worthExtracting = ! $this->extractorIsPaid
            || ($score >= self::EXTRACT_MIN_SCORE && ! array_intersect($moderation, ['spam', 'insulto']));
        $x = $worthExtracting ? $this->extractor->classify($text, $ctx) : null;

        return new Classification(
            intent: in_array($intent->value, Classification::INTENTS, true) ? $intent->value : 'otro',
            sentiment: in_array($sentiment->value, Classification::SENTIMENTS, true) ? $sentiment->value : 'neutral',
            topic: $x?->topic,
            location: $x?->location,
            name: $x?->name,
            onAirScore: $score,
            moderation: $moderation,
            summary: $x?->summary ?? mb_substr($text, 0, 120),
            raw: [
                'decider' => $result->model,
                'decider_ms' => $ms,
                'decider_tokens' => $result->usage->inputTokens,
                'decider_cost_usd' => $result->usage->costUsd(),
                'confidence' => [
                    'intent' => round($intent->confidence, 2),
                    'sentiment' => round($sentiment->confidence, 2),
                    'on_air' => round($onAir->confidence, 2),
                ],
                'moderation_p' => array_combine($flags, array_map(fn ($f) => round($result->noul($f)->probability, 2), $flags)),
                'review' => $review,
                'extracted' => $worthExtracting,
                'extractor' => $x?->raw,
            ],
        );
    }
}
