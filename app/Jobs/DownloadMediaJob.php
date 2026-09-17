<?php

namespace App\Jobs;

use App\Events\MessageUpdated;
use App\Models\Message;
use App\Wa\WaProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DownloadMediaJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    public array $backoff = [2, 5, 15, 30];

    public function __construct(public Message $message) {}

    public function handle(WaProvider $wa): void
    {
        if ($this->message->media_path) {
            $this->next();

            return;
        }

        if ($this->message->provider === 'fake') {
            $wa = new \App\Wa\FakeProvider;
        }
        $media = $wa->downloadMedia($this->message->wa_message_id);
        $path = sprintf('media/%s/%d.%s', $this->message->sent_at->format('Y/m'), $this->message->id, $media['ext']);
        Storage::disk('local')->put($path, $media['bytes']);

        $this->message->update([
            'media_path' => $path,
            'media_mime' => $media['mime'],
            'status' => Message::STATUS_MEDIA,
        ]);
        broadcast(new MessageUpdated($this->message));

        $this->next();
    }

    private function next(): void
    {
        if ($this->message->isAudio()) {
            TranscribeMessageJob::dispatch($this->message)->onQueue('stt');
        } elseif ($this->message->textForAnalysis()) {
            ClassifyMessageJob::dispatch($this->message)->onQueue('classify');
        } else {
            $this->message->update(['status' => Message::STATUS_READY]);
            broadcast(new MessageUpdated($this->message));
        }
    }

    public function failed(Throwable $e): void
    {
        $this->message->update(['status' => Message::STATUS_FAILED, 'error' => 'media: '.$e->getMessage()]);
        broadcast(new MessageUpdated($this->message));
    }
}
