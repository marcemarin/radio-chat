<?php

namespace App\Stt;

use Illuminate\Support\Facades\Http;

class DeepgramTranscriber implements Transcriber
{
    public function __construct(private readonly string $key) {}

    public function name(): string
    {
        return 'deepgram';
    }

    public function transcribe(string $absPath, string $mime, string $lang = 'es'): Transcript
    {
        $t0 = hrtime(true);
        $res = Http::withHeaders(['Authorization' => 'Token '.$this->key])
            ->timeout(120)
            ->withBody(file_get_contents($absPath), $mime ?: 'audio/ogg')
            ->post('https://api.deepgram.com/v1/listen?'.http_build_query([
                'model' => 'nova-3',
                'language' => $lang,
                'smart_format' => 'true',
                'punctuate' => 'true',
            ]))
            ->throw()
            ->json();

        $alt = $res['results']['channels'][0]['alternatives'][0] ?? [];

        return new Transcript(
            text: trim((string) ($alt['transcript'] ?? '')),
            provider: $this->name(),
            ms: (int) ((hrtime(true) - $t0) / 1e6),
            confidence: isset($alt['confidence']) ? (float) $alt['confidence'] : null,
            raw: ['duration' => $res['metadata']['duration'] ?? null],
        );
    }
}
