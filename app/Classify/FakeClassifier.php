<?php

namespace App\Classify;

/** Heurístico para desarrollar sin API key. */
class FakeClassifier implements Classifier
{
    public function classify(string $text, array $ctx = []): Classification
    {
        $t = mb_strtolower($text);
        $intent = match (true) {
            preg_match('/promo|% ?off|llam[aá] ya|oferta/', $t) === 1 => 'spam',
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
        preg_match('/(?<!tema nuevo )(?<!algo )(?:\bde|\bdesde)\s+((?!Los\b|Las\b|El\b|La\b)[A-ZÁÉÍÓÚ][\wáéíóúñ]+(?:\s+[A-ZÁÉÍÓÚ][\wáéíóúñ]+)?)/u', $text, $loc);
        preg_match('/(?:soy|habla|me llamo)\s+([A-ZÁÉÍÓÚ][\wáéíóúñ]+)/u', $text, $name);

        return new Classification(
            intent: $intent,
            sentiment: $sentiment,
            topic: self::topicFor($t, $intent),
            location: $loc[1] ?? null,
            name: $name[1] ?? null,
            onAirScore: min(100, 40 + (isset($loc[1]) ? 20 : 0) + (isset($name[1]) ? 20 : 0) + (mb_strlen($text) < 200 ? 10 : 0)),
            moderation: array_values(array_filter([preg_match('/pelotud|boludo|mierda|forro/', $t) ? 'insulto' : null, $intent === 'spam' ? 'spam' : null])),
            summary: mb_substr($text, 0, 110),
        );
    }

    private static function topicFor(string $t, string $intent): string
    {
        return match (true) {
            str_contains($t, 'sin luz') || str_contains($t, 'corte de luz') => 'corte de luz',
            (bool) preg_match('/calle|bache|intransit|destruida/', $t) => 'estado de las calles',
            (bool) preg_match('/colectivo|transporte|el 28|boleto/', $t) => 'transporte público',
            (bool) preg_match('/puente|tránsito|transito|cortad/', $t) => 'tránsito y cortes',
            (bool) preg_match('/agua|basura|cloaca/', $t) => 'servicios públicos',
            $intent === 'concurso' => 'sorteo de entradas',
            $intent === 'pedido_musical' => 'pedidos musicales',
            $intent === 'saludo' => 'saludos y cumpleaños',
            $intent === 'spam' => 'publicidad',
            default => 'comentarios del programa',
        };
    }
}
