<?php

namespace App\Models\Concerns;

use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Lets a model carry many categories from the shared taxonomy.
 */
trait HasCategories
{
    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable')
            ->withPivot('sort')
            ->withTimestamps()
            ->orderByPivot('sort');
    }
}
