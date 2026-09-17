<?php

namespace App\Stt;

use InvalidArgumentException;

class SttManager
{
    /** @var array<string, Transcriber> */
    private array $drivers = [];

    public function driver(?string $name = null): Transcriber
    {
        $name ??= config('services.stt.driver', 'fake');

        return $this->drivers[$name] ??= $this->make($name);
    }

    /** Drivers con API key configurada (para el benchmark). */
    public function available(): array
    {
        $c = config('services.stt');
        $out = [];
        if ($c['assemblyai_key']) { $out[] = 'assemblyai'; }
        if ($c['deepgram_key']) { $out[] = 'deepgram'; }
        if ($c['openai_key']) { $out[] = 'openai'; }

        return $out;
    }

    private function make(string $name): Transcriber
    {
        $c = config('services.stt');

        return match ($name) {
            'assemblyai' => new AssemblyAiTranscriber((string) $c['assemblyai_key']),
            'deepgram' => new DeepgramTranscriber((string) $c['deepgram_key']),
            'openai' => new OpenAiTranscriber((string) $c['openai_key']),
            'fake' => new FakeTranscriber,
            default => throw new InvalidArgumentException("STT driver desconocido: {$name}"),
        };
    }
}
