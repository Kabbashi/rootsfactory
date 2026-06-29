<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['theme_id', 'name', 'description'];

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function codes(): HasMany
    {
        return $this->hasMany(Code::class);
    }
}
