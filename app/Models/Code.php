<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Code extends Model
{
    protected $fillable = ['category_id', 'name', 'color', 'description'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function dataItems(): BelongsToMany
    {
        return $this->belongsToMany(DataItem::class, 'codings')
            ->withPivot('excerpt', 'user_id')
            ->withTimestamps();
    }
}
