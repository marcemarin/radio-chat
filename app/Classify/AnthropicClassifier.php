<?php

namespace App\Classify;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicClassifier implements Classifier
{
    public function __construct(private readonly string $key, private readonly string $model) {}

    public function classify(string $text, array $ctx = []): Classification
    {
        $tool = [
            'name' => 'clasificar_mensaje',
            'description' => 'Clasifica un mensaje de oyente recibido por WhatsApp durante un programa de radio.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'intent' => ['type' => 'string', 'enum' => Classification::INTENTS],
                    'sentiment' => ['type' => 'string', 'enum' => Classification::SENTIMENTS],
                    'topic' => ['type' => 'string', 'description' => 'Tema en 3 a 6 palabras, en español, sin nombre propio del oyente. Ej: "corte de luz en Villa Cabello".'],
                    'location' => ['type' => ['string', 'null'], 'description' => 'Ciudad/barrio/localidad mencionada por el oyente, o null.'],
                    'name' => ['type' => ['string', 'null'], 'description' => 'Nombre con que se presenta el oyente, o null.'],
                    'on_air_score' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100, 'description' => 'Qué tan bueno es para salir al aire: claro, breve, con nombre y lugar, emocional o informativo. 0 = spam/insulto, 100 = perfecto.'],
                    'moderation' => ['type' => 'array', 'items' => ['type' => 'string', 'enum' => ['insulto', 'spam', 'datos_personales', 'contenido_sensible']]],
                    'summary' => ['type' => 'string', 'description' => 'Resumen de una línea (máx 120 caracteres) para que el productor lo lea de un vistazo.'],
                ],
                'required' => ['intent', 'sentiment', 'topic', 'on_air_score', 'moderation', 'summary'],
            ],
        ];

        $system = implode("\n", array_filter([
            'Sos el asistente de producción de un programa de radio en Argentina. Recibís mensajes de oyentes por WhatsApp (muchos son transcripciones automáticas de audios, con errores).',
            $ctx['program'] ? "Programa: {$ctx['program']}".($ctx['station'] ? " ({$ctx['station']})" : '') : null,
            $ctx['context'] ? "Contexto del programa: {$ctx['context']}" : null,
            'Clasificá con criterio de productor: qué es, de qué habla, de dónde escribe, y si vale la pena ponerlo al aire.',
            'Definiciones: opinion = comentario sobre un tema; pedido_musical = pide una canción o artista; saludo = solo saluda/agradece; reclamo = denuncia o queja sobre un servicio, calle, institución; concurso = participa o pregunta por un sorteo/concurso; consulta = pregunta información; spam = publicidad, cadenas, sin sentido.',
            'Si el texto está vacío, es ruido o ininteligible: intent=otro, on_air_score=0.',
        ]));

        $user = ($ctx['is_audio'] ?? false ? "[Transcripción de audio]\n" : "[Texto]\n")
            .($ctx['contact_name'] ?? null ? "Nombre en WhatsApp: {$ctx['contact_name']}\n" : '')
            ."Mensaje:\n".$text;

        $res = Http::withHeaders([
            'x-api-key' => $this->key,
            'anthropic-version' => '2023-06-01',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->model,
            'max_tokens' => 400,
            'system' => $system,
            'tools' => [$tool],
            'tool_choice' => ['type' => 'tool', 'name' => 'clasificar_mensaje'],
            'messages' => [['role' => 'user', 'content' => $user]],
        ])->throw()->json();

        $input = collect($res['content'] ?? [])->firstWhere('type', 'tool_use')['input'] ?? null;
        if (! is_array($input)) {
            throw new RuntimeException('Anthropic no devolvió tool_use');
        }

        return new Classification(
            intent: in_array($input['intent'] ?? null, Classification::INTENTS, true) ? $input['intent'] : 'otro',
            sentiment: in_array($input['sentiment'] ?? null, Classification::SENTIMENTS, true) ? $input['sentiment'] : 'neutral',
            topic: self::str($input['topic'] ?? null, 80),
            location: self::str($input['location'] ?? null, 80),
            name: self::str($input['name'] ?? null, 60),
            onAirScore: max(0, min(100, (int) ($input['on_air_score'] ?? 0))),
            moderation: array_values(array_filter((array) ($input['moderation'] ?? []), 'is_string')),
            summary: self::str($input['summary'] ?? null, 160),
            raw: ['usage' => $res['usage'] ?? null, 'model' => $res['model'] ?? $this->model],
        );
    }

    private static function str(mixed $v, int $max): ?string
    {
        $v = is_string($v) ? trim($v) : null;

        return $v === '' || $v === null ? null : mb_substr($v, 0, $max);
    }
}
