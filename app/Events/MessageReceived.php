<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Llegó un mensaje nuevo (todavía sin procesar): la Sala lo muestra al instante. */
class MessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [new Channel("sala.{$this->message->program_id}")];
    }

    public function broadcastAs(): string
    {
        return 'message.received';
    }

    public function broadcastWith(): array
    {
        return ['message' => $this->message->toSala()];
    }
}
