<?php

namespace App\Models\Concerns;

use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Lets ideas, concepts and projects carry delegatable tasks.
 */
trait HasTasks
{
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }
}
