<?php

namespace App\Jobs;

use App\Events\MessageUpdated;
use App\Models\Message;
use App\Stt\SttManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TranscribeMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [3, 10, 30];

    public int $timeout = 180;

    public function __construct(public Message $message) {}

    public function handle(SttManager $stt): void
    {
        if ($this->message->transcript !== null) {
            ClassifyMessageJob::dispatch($this->message)->onQueue('classify');

            return;
        }

        $this->message->update(['status' => Message::STATUS_TRANSCRIBING]);
        broadcast(new MessageUpdated($this->message));

        $path = Storage::disk('local')->path($this->message->media_path);
        $t = $stt->driver()->transcribe($path, $this->message->media_mime ?? 'audio/ogg');

        $this->message->update([
            'transcript' => $t->text,
            'transcript_provider' => $t->provider,
            'transcript_ms' => $t->ms,
        ]);
        broadcast(new MessageUpdated($this->message));

        if ($this->message->textForAnalysis()) {
            ClassifyMessageJob::dispatch($this->message)->onQueue('classify');
        } else {
            $this->message->update(['status' => Message::STATUS_READY]);
            broadcast(new MessageUpdated($this->message));
        }
    }

    public function failed(Throwable $e): void
    {
        $this->message->update(['status' => Message::STATUS_FAILED, 'error' => 'stt: '.$e->getMessage()]);
        broadcast(new MessageUpdated($this->message));
    }
}
