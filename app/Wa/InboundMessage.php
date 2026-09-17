<?php

namespace App\Wa;

use Carbon\CarbonImmutable;

/** Mensaje entrante normalizado, independiente del proveedor (Evolution hoy, Meta Cloud en el piloto). */
final readonly class InboundMessage
{
    public function __construct(
        public string $provider,
        public string $waMessageId,
        public string $phone,
        public ?string $pushName,
        public string $type,
        public ?string $body,
        public ?string $mediaMime,
        public ?int $mediaDurationS,
        public CarbonImmutable $sentAt,
        public array $raw = [],
    ) {}
}
