<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Region extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    protected static function booted(): void
    {
        static::saving(function (Region $region) {
            if (blank($region->slug)) {
                $region->slug = Str::slug($region->name);
            }
        });
    }

    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class);
    }
}
