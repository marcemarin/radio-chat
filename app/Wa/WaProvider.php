<?php

namespace App\Wa;

interface WaProvider
{
    /** Crea/conecta la instancia. Devuelve ['state' => string, 'qr' => ?string (data URI png)]. */
    public function connect(): array;

    /** open | connecting | close | unknown */
    public function state(): string;

    /** Descarga el media de un mensaje. Devuelve ['bytes' => string, 'mime' => string, 'ext' => string]. */
    public function downloadMedia(string $waMessageId): array;
}
