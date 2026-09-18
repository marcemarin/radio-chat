<?php

namespace App\Wa;

use App\Models\Message;
use RuntimeException;

/** Sirve como "proveedor" de los mensajes generados por `radio:fake-burst`: el media sale de un archivo local. */
class FakeProvider implements WaProvider
{
    public function connect(): array
    {
        return ['state' => 'open', 'qr' => null];
    }

    public function state(): string
    {
        return 'open';
    }

    public function downloadMedia(string $waMessageId, array $raw = []): array
    {
        $file = cache()->pull("fake-audio:{$waMessageId}");
        if (! $file || ! is_file($file)) {
            throw new RuntimeException("Sin audio falso para {$waMessageId}");
        }
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        return [
            'bytes' => file_get_contents($file),
            'mime' => match ($ext) { 'mp3' => 'audio/mpeg', 'm4a' => 'audio/mp4', default => 'audio/ogg' },
            'ext' => $ext ?: 'ogg',
        ];
    }
}
