<?php

namespace App\Stt;

final readonly class Transcript
{
    public function __construct(
        public string $text,
        public string $provider,
        public int $ms,
        public ?float $confidence = null,
        public array $raw = [],
    ) {}
}
