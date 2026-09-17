<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Highlight extends Model
{
    protected $fillable = ['program_id', 'message_id', 'position', 'status', 'note'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
