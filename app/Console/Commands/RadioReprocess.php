<?php

namespace App\Console\Commands;

use App\Jobs\ClassifyMessageJob;
use App\Jobs\TranscribeMessageJob;
use App\Models\Message;
use Illuminate\Console\Command;

class RadioReprocess extends Command
{
    protected $signature = 'radio:reprocess {--stt : volver a transcribir los audios} {--since= : solo mensajes desde esta fecha (Y-m-d)} {--failed : incluir los fallidos}';

    protected $description = 'Vuelve a pasar mensajes por transcripción y/o clasificación (por ejemplo, al cambiar de driver o cargar API keys)';

    public function handle(): int
    {
        $q = Message::query()->orderBy('id');
        if ($since = $this->option('since')) {
            $q->where('sent_at', '>=', $since);
        }
        if (! $this->option('failed')) {
            $q->where('status', '!=', Message::STATUS_FAILED);
        }

        $n = 0;
        $q->chunkById(200, function ($messages) use (&$n) {
            foreach ($messages as $m) {
                if ($this->option('stt') && $m->isAudio() && $m->media_path) {
                    $m->update(['transcript' => null, 'transcript_provider' => null, 'status' => Message::STATUS_MEDIA, 'error' => null]);
                    TranscribeMessageJob::dispatch($m)->onQueue('stt');
                    $n++;
                } elseif ($m->textForAnalysis()) {
                    ClassifyMessageJob::dispatch($m)->onQueue('classify');
                    $n++;
                }
            }
        });
        $this->info("Encolados {$n} mensajes.");

        return self::SUCCESS;
    }
}
