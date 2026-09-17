<?php

namespace App\Stt;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AssemblyAiTranscriber implements Transcriber
{
    public function __construct(private readonly string $key) {}

    public function name(): string
    {
        return 'assemblyai';
    }

    public function transcribe(string $absPath, string $mime, string $lang = 'es'): Transcript
    {
        $t0 = hrtime(true);
        $http = Http::baseUrl('https://api.assemblyai.com/v2')->withHeaders(['authorization' => $this->key])->timeout(120);

        $upload = $http->withBody(file_get_contents($absPath), 'application/octet-stream')->post('/upload')->throw();
        $job = $http->post('/transcript', [
            'audio_url' => $upload->json('upload_url'),
            'language_code' => $lang,
            'speech_model' => 'universal',
            'punctuate' => true,
            'format_text' => true,
        ])->throw();

        $id = $job->json('id');
        $deadline = time() + 150;
        do {
            usleep(800_000);
            $res = $http->get("/transcript/{$id}")->throw()->json();
            $status = $res['status'] ?? 'error';
            if ($status === 'error') {
                throw new RuntimeException('AssemblyAI: '.($res['error'] ?? 'error'));
            }
        } while ($status !== 'completed' && time() < $deadline);

        if ($status !== 'completed') {
            throw new RuntimeException('AssemblyAI: timeout esperando la transcripción');
        }

        return new Transcript(
            text: trim((string) ($res['text'] ?? '')),
            provider: $this->name(),
            ms: (int) ((hrtime(true) - $t0) / 1e6),
            confidence: isset($res['confidence']) ? (float) $res['confidence'] : null,
            raw: ['id' => $id, 'audio_duration' => $res['audio_duration'] ?? null],
        );
    }
}
