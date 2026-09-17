<?php

namespace App\Stt;

use Illuminate\Support\Facades\Http;

class OpenAiTranscriber implements Transcriber
{
    public function __construct(private readonly string $key, private readonly string $model = 'gpt-4o-mini-transcribe') {}

    public function name(): string
    {
        return 'openai';
    }

    public function transcribe(string $absPath, string $mime, string $lang = 'es'): Transcript
    {
        $t0 = hrtime(true);
        $res = Http::withToken($this->key)
            ->timeout(120)
            ->attach('file', file_get_contents($absPath), basename($absPath))
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => $this->model,
                'language' => $lang,
                'response_format' => 'json',
            ])
            ->throw()
            ->json();

        return new Transcript(
            text: trim((string) ($res['text'] ?? '')),
            provider: $this->name(),
            ms: (int) ((hrtime(true) - $t0) / 1e6),
            raw: [],
        );
    }
}
