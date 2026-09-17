<?php

namespace App\Jobs;

use App\Events\MessageReceived;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Program;
use App\Wa\InboundMessage;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class IngestMessageJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public InboundMessage $in) {}

    public function uniqueId(): string
    {
        return $this->in->waMessageId;
    }

    public function handle(): void
    {
        if (Message::where('wa_message_id', $this->in->waMessageId)->exists()) {
            return;
        }

        $message = DB::transaction(function () {
            $contact = Contact::firstOrCreate(
                ['phone' => $this->in->phone],
                ['name' => $this->in->pushName, 'first_seen_at' => $this->in->sentAt]
            );
            $contact->forceFill([
                'name' => $this->in->pushName ?: $contact->name,
                'last_seen_at' => $this->in->sentAt,
                'messages_count' => $contact->messages_count + 1,
            ])->save();

            return Message::create([
                'program_id' => Program::default()->id,
                'contact_id' => $contact->id,
                'provider' => $this->in->provider,
                'wa_message_id' => $this->in->waMessageId,
                'type' => $this->in->type,
                'body' => $this->in->body,
                'media_mime' => $this->in->mediaMime,
                'media_duration_s' => $this->in->mediaDurationS,
                'status' => Message::STATUS_RECEIVED,
                'sent_at' => $this->in->sentAt,
            ]);
        });

        broadcast(new MessageReceived($message));

        if ($message->hasMedia()) {
            DownloadMediaJob::dispatch($message)->onQueue('media');
        } elseif ($message->textForAnalysis()) {
            ClassifyMessageJob::dispatch($message)->onQueue('classify');
        } else {
            $message->update(['status' => Message::STATUS_READY]);
        }
    }
}
