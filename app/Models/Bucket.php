<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A column on the task board. Three are system buckets tied to a subject type
 * (idea/concept/project); the rest are custom buckets members add themselves.
 */
class Bucket extends Model
{
    protected $fillable = ['name', 'system_type', 'sort'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function isSystem(): bool
    {
        return $this->system_type !== null;
    }

    /** The system bucket for a subject type ('idea'|'concept'|'project'). */
    public static function forType(string $type): ?self
    {
        return static::where('system_type', $type)->first();
    }
}
