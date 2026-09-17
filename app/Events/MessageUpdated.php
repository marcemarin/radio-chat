<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Un mensaje avanzó en el pipeline (media, transcripción, clasificación, destacado). */
class MessageUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [new Channel("sala.{$this->message->program_id}")];
    }

    public function broadcastAs(): string
    {
        return 'message.updated';
    }

    public function broadcastWith(): array
    {
        return ['message' => $this->message->toSala()];
    }
}
