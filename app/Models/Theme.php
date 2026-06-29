<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    protected $fillable = ['name', 'description'];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
