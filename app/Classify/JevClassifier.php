<?php

namespace App\Classify;

use Illuminate\Support\Facades\Http;

/**
 * Clasificador híbrido.
 *
 * Las decisiones (intención, tono, puntaje para el aire, moderación) las toma Jev, un modelo de
 * decisiones tipadas de TypeSafe AI: rápido, barato y con probabilidades calibradas.
 * Lo que requiere generar o extraer texto (tema, lugar, nombre, resumen) se delega a otro
 * Classifier, y solo para los mensajes que valen la pena.
 */
class JevClassifier implements Classifier
{
    private const ENDPOINT = 'https://api.typesafe.ai/v1/systemone';

    /** Por debajo de este puntaje (0-100) no se gasta en extracción con LLM. */
    private const EXTRACT_MIN_SCORE = 40;

    public function __construct(
        private readonly string $key,
        private readonly string $model,
        private readonly Classifier $extractor,
        private readonly bool $extractorIsPaid,
    ) {}

    public function classify(string $text, array $ctx = []): Classification
    {
        $t0 = hrtime(true);
        $res = Http::withToken($this->key)
            ->timeout(20)
            ->retry(3, 600, fn ($e) => in_array($e->response?->status(), [429, 529], true), throw: true)
            ->post(self::ENDPOINT, [
                'model' => $this->model,
                'state' => array_filter([
                    'message' => $text,
                    'is_voice_note_transcript' => (bool) ($ctx['is_audio'] ?? false),
                    'whatsapp_name' => $ctx['contact_name'] ?? null,
                    'program' => trim(($ctx['program'] ?? '').' '.($ctx['station'] ?? '')) ?: null,
                    'program_context' => $ctx['context'] ?? null,
                ], fn ($v) => $v !== null),
                'questions' => self::questions(),
            ])
            ->throw()
            ->json();
        $ms = (int) ((hrtime(true) - $t0) / 1e6);

        $a = $res['answers'] ?? [];
        $intent = $a['intent']['choice'] ?? 'otro';
        $sentiment = $a['sentiment']['choice'] ?? 'neutral';
        $levels = max(1, count($a['on_air']['legend'] ?? [0, 1, 2, 3, 4]) - 1);
        $score = (int) round(max(0, min(1, ((float) ($a['on_air']['score'] ?? 0)) / $levels)) * 100);

        $nouls = [
            'insulto' => (float) ($a['insulto']['noul'] ?? 0),
            'spam' => (float) ($a['spam']['noul'] ?? 0),
            'datos_personales' => (float) ($a['datos_personales']['noul'] ?? 0),
        ];
        $moderation = array_keys(array_filter($nouls, fn ($p) => $p >= 0.6));
        if ($intent === 'spam' && ! in_array('spam', $moderation, true)) {
            $moderation[] = 'spam';
        }
        $unsure = array_keys(array_filter($nouls, fn ($p) => $p >= 0.4 && $p < 0.6));

        $worthExtracting = ! $this->extractorIsPaid
            || ($score >= self::EXTRACT_MIN_SCORE && ! array_intersect($moderation, ['spam', 'insulto']));
        $x = $worthExtracting ? $this->extractor->classify($text, $ctx) : null;

        return new Classification(
            intent: in_array($intent, Classification::INTENTS, true) ? $intent : 'otro',
            sentiment: in_array($sentiment, Classification::SENTIMENTS, true) ? $sentiment : 'neutral',
            topic: $x?->topic,
            location: $x?->location,
            name: $x?->name,
            onAirScore: $score,
            moderation: array_values($moderation),
            summary: $x?->summary ?? mb_substr($text, 0, 120),
            raw: [
                'decider' => $res['model'] ?? $this->model,
                'decider_ms' => $ms,
                'decider_tokens' => $res['usage']['input_tokens'] ?? null,
                'confidence' => [
                    'intent' => round((float) ($a['intent']['confidence'] ?? 0), 2),
                    'sentiment' => round((float) ($a['sentiment']['confidence'] ?? 0), 2),
                    'on_air' => round((float) ($a['on_air']['confidence'] ?? 0), 2),
                ],
                'moderation_p' => array_map(fn ($p) => round($p, 2), $nouls),
                'review' => $unsure,
                'extracted' => $worthExtracting,
                'extractor' => $x?->raw,
            ],
        );
    }

    /** Preguntas en inglés (el idioma más preciso del modelo) sobre un mensaje en español rioplatense. */
    private static function questions(): array
    {
        return [
            'intent' => [
                'type' => 'choice',
                'instructions' => "What is the listener's main purpose in `message`? It is a WhatsApp message in Argentine Spanish sent to a live radio show; it may be an automatic transcription of a voice note, with errors.",
                'criteria' => [
                    'reclamo' => 'Complains about or reports a problem with a public service, street, institution or company',
                    'pedido_musical' => 'Asks the show to play a song or artist',
                    'saludo' => 'Only greets, thanks, congratulates or sends regards',
                    'opinion' => 'Gives an opinion or comment about a topic being discussed',
                    'concurso' => 'Participates in or asks about a contest, raffle or giveaway',
                    'consulta' => 'Asks for information',
                    'spam' => 'Advertising, chain messages, scams or content unrelated to the show',
                    'otro' => 'None of the above, or unintelligible',
                ],
            ],
            'sentiment' => [
                'type' => 'choice',
                'instructions' => 'What is the emotional tone of `message`?',
                'criteria' => [
                    'positivo' => 'Happy, grateful, enthusiastic',
                    'neutral' => 'Neutral or purely informative',
                    'negativo' => 'Angry, upset, worried or complaining',
                ],
            ],
            'on_air' => [
                'type' => 'score',
                'instructions' => 'How good is `message` to be read or played on air by a radio producer?',
                'criteria' => [
                    'Unusable: spam, insults, unintelligible or empty',
                    'Weak: generic, no information, nothing the audience would care about',
                    'Acceptable: clear but ordinary',
                    'Good: clear, brief, relevant to listeners, the person says who they are or where they are from',
                    "Excellent: clear, brief, emotional or newsworthy, with the listener's name and location",
                ],
            ],
            'insulto' => ['type' => 'noul', 'instructions' => 'Does `message` contain insults, slurs or profanity directed at someone?'],
            'spam' => ['type' => 'noul', 'instructions' => 'Is `message` advertising, a scam or a chain message?'],
            'datos_personales' => ['type' => 'noul', 'instructions' => 'Does `message` contain a phone number, home address, ID number or other private personal data?'],
        ];
    }
}
