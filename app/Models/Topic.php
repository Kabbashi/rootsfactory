<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Topic extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    protected static function booted(): void
    {
        static::saving(function (Topic $topic) {
            if (blank($topic->slug)) {
                $topic->slug = Str::slug($topic->name);
            }
        });

        // Polymorphic comments have no DB-level cascade — clean them up here.
        static::deleting(fn (Topic $topic) => $topic->comments()->delete());
    }

    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
