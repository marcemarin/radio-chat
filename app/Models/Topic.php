<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    protected $fillable = ['program_id', 'show_id', 'label', 'messages_count', 'first_at', 'last_at'];

    protected $casts = ['first_at' => 'datetime', 'last_at' => 'datetime'];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
