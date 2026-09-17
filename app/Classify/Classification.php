<?php

namespace App\Classify;

final readonly class Classification
{
    public const INTENTS = ['opinion', 'pedido_musical', 'saludo', 'reclamo', 'concurso', 'consulta', 'spam', 'otro'];

    public const SENTIMENTS = ['positivo', 'neutral', 'negativo'];

    public function __construct(
        public string $intent,
        public string $sentiment,
        public ?string $topic,
        public ?string $location,
        public ?string $name,
        public int $onAirScore,
        public array $moderation,
        public ?string $summary,
        public array $raw = [],
    ) {}
}
