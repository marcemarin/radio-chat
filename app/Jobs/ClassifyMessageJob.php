<?php

namespace App\Jobs;

use App\Classify\Classifier;
use App\Events\MessageUpdated;
use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ClassifyMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [3, 10, 30];

    public function __construct(public Message $message) {}

    public function handle(Classifier $classifier): void
    {
        $text = $this->message->textForAnalysis();
        if ($text === null) {
            $this->message->update(['status' => Message::STATUS_READY]);
            broadcast(new MessageUpdated($this->message));

            return;
        }

        $this->message->update(['status' => Message::STATUS_CLASSIFYING]);

        $this->message->loadMissing(['program', 'contact']);
        $c = $classifier->classify($text, [
            'program' => $this->message->program->name,
            'station' => $this->message->program->station,
            'context' => $this->message->program->context,
            'contact_name' => $this->message->contact->label(),
            'is_audio' => $this->message->isAudio(),
            'phone_prefix' => substr($this->message->contact->phone, 0, 6),
        ]);

        $this->message->update([
            'intent' => $c->intent,
            'sentiment' => $c->sentiment,
            'topic_label' => $c->topic,
            'location' => $c->location,
            'on_air_score' => $c->onAirScore,
            'moderation' => $c->moderation,
            'classification' => ['summary' => $c->summary, 'name' => $c->name, 'raw' => $c->raw],
            'status' => Message::STATUS_READY,
        ]);

        if ($c->location && ! $this->message->contact->location) {
            $this->message->contact->update(['location' => $c->location]);
        }
        if ($c->name && ! $this->message->contact->display_name && ! $this->message->contact->name) {
            $this->message->contact->update(['display_name' => $c->name]);
        }

        broadcast(new MessageUpdated($this->message->fresh()));
    }

    public function failed(Throwable $e): void
    {
        $this->message->update(['status' => Message::STATUS_FAILED, 'error' => 'classify: '.$e->getMessage()]);
        broadcast(new MessageUpdated($this->message));
    }
}
