<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Message extends Model
{
    public const STATUS_RECEIVED = 'received';
    public const STATUS_MEDIA = 'media';
    public const STATUS_TRANSCRIBING = 'transcribing';
    public const STATUS_CLASSIFYING = 'classifying';
    public const STATUS_READY = 'ready';
    public const STATUS_FAILED = 'failed';

    protected $guarded = ['id'];

    protected $casts = [
        'sent_at' => 'datetime',
        'moderation' => 'array',
        'classification' => 'array',
        'raw' => 'array',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function highlight(): HasOne
    {
        return $this->hasOne(Highlight::class);
    }

    public function isAudio(): bool
    {
        return $this->type === 'audio';
    }

    public function hasMedia(): bool
    {
        return in_array($this->type, ['audio', 'image', 'video', 'document'], true);
    }

    /** Texto sobre el que se clasifica: transcripción si es audio, cuerpo si no. */
    public function textForAnalysis(): ?string
    {
        $text = trim((string) ($this->transcript ?? $this->body ?? ''));

        return $text === '' ? null : $text;
    }

    /** Representación que consume la Sala (API + eventos Reverb). */
    public function toSala(): array
    {
        $this->loadMissing(['contact', 'highlight']);

        return [
            'id' => $this->id,
            'program_id' => $this->program_id,
            'type' => $this->type,
            'status' => $this->status,
            'contact' => [
                'id' => $this->contact->id,
                'phone' => $this->contact->phone,
                'name' => $this->contact->label(),
                'messages_count' => $this->contact->messages_count,
            ],
            'body' => $this->body,
            'transcript' => $this->transcript,
            'transcript_provider' => $this->transcript_provider,
            'media_url' => $this->media_path ? url("/media/{$this->id}") : null,
            'media_mime' => $this->media_mime,
            'media_duration_s' => $this->media_duration_s,
            'intent' => $this->intent,
            'sentiment' => $this->sentiment,
            'topic_label' => $this->topic_label,
            'topic_id' => $this->topic_id,
            'location' => $this->location,
            'on_air_score' => $this->on_air_score,
            'moderation' => $this->moderation ?? [],
            'summary' => $this->classification['summary'] ?? null,
            'highlight' => $this->highlight ? ['id' => $this->highlight->id, 'status' => $this->highlight->status] : null,
            'error' => $this->error,
            'sent_at' => $this->sent_at?->toIso8601String(),
        ];
    }
}
