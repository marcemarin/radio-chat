<?php

namespace App\Classify;

/** Heurístico para desarrollar sin API key. */
class FakeClassifier implements Classifier
{
    public function classify(string $text, array $ctx = []): Classification
    {
        $t = mb_strtolower($text);
        $intent = match (true) {
            preg_match('/pasar|tema|canci|artista|palmeras|cumbia/', $t) === 1 => 'pedido_musical',
            preg_match('/sorteo|concurso|entrada|particip/', $t) === 1 => 'concurso',
            preg_match('/sin luz|calle|denunci|reclam|intransit|nadie viene|basura|agua/', $t) === 1 => 'reclamo',
            preg_match('/\?|cómo|como|cuándo|dónde/', $t) === 1 => 'consulta',
            preg_match('/saludo|abrazo|buen día|buenas|gracias|los escucho/', $t) === 1 => 'saludo',
            default => 'opinion',
        };
        $sentiment = match (true) {
            $intent === 'reclamo' => 'negativo',
            in_array($intent, ['saludo', 'pedido_musical'], true) => 'positivo',
            default => 'neutral',
        };
        preg_match('/(?:de|desde|en)\s+([A-ZÁÉÍÓÚ][\wáéíóúñ]+(?:\s+[A-ZÁÉÍÓÚ][\wáéíóúñ]+)?)/u', $text, $loc);
        preg_match('/(?:soy|habla|me llamo)\s+([A-ZÁÉÍÓÚ][\wáéíóúñ]+)/u', $text, $name);

        return new Classification(
            intent: $intent,
            sentiment: $sentiment,
            topic: mb_substr(preg_replace('/^(hola|buen d[ií]a|buenas|che)[,\s]*/iu', '', $text), 0, 40),
            location: $loc[1] ?? null,
            name: $name[1] ?? null,
            onAirScore: min(100, 40 + (isset($loc[1]) ? 20 : 0) + (isset($name[1]) ? 20 : 0) + (mb_strlen($text) < 200 ? 10 : 0)),
            moderation: preg_match('/pelotud|boludo|mierda|forro/', $t) ? ['insulto'] : [],
            summary: mb_substr($text, 0, 110),
        );
    }
}
