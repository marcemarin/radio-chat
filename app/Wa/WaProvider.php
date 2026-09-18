<?php

namespace App\Wa;

interface WaProvider
{
    /** Crea/conecta la instancia. Devuelve ['state' => string, 'qr' => ?string (data URI png)]. */
    public function connect(): array;

    /** open | connecting | close | unknown */
    public function state(): string;

    /**
     * Descarga el media de un mensaje. `$raw` es el payload crudo guardado al ingresar (key + message);
     * Evolution lo usa para bajar el archivo sin tener el mensaje en su propia DB.
     * Devuelve ['bytes' => string, 'mime' => string, 'ext' => string].
     */
    public function downloadMedia(string $waMessageId, array $raw = []): array;
}
