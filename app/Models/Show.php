<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Show extends Model
{
    protected $fillable = ['program_id', 'title', 'started_at', 'ended_at'];

    protected $casts = ['started_at' => 'datetime', 'ended_at' => 'datetime'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
