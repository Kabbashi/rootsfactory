<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    }

    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class);
    }
}
