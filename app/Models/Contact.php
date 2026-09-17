<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $fillable = ['phone', 'name', 'display_name', 'location', 'messages_count', 'first_seen_at', 'last_seen_at'];

    protected $casts = ['first_seen_at' => 'datetime', 'last_seen_at' => 'datetime'];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function label(): string
    {
        return $this->display_name ?: ($this->name ?: $this->phone);
    }
}
