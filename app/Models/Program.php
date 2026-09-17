<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Program extends Model
{
    protected $fillable = ['name', 'slug', 'station', 'context'];

    public static function default(): self
    {
        return static::query()->orderBy('id')->firstOr(fn () => static::create([
            'name' => 'Demo',
            'slug' => 'demo',
            'station' => 'Radio Chat',
        ]));
    }

    public static function bySlugOrCreate(string $name): self
    {
        $slug = Str::slug($name);

        return static::firstOrCreate(['slug' => $slug], ['name' => $name]);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(Highlight::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }
}
