<?php

namespace App\Models\Concerns;

use App\Models\Keyword;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Lets a model carry many keywords from the shared vocabulary.
 */
trait HasKeywords
{
    public function keywords(): MorphToMany
    {
        return $this->morphToMany(Keyword::class, 'keywordable')
            ->withTimestamps()
            ->orderBy('name');
    }
}
