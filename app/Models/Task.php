<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    public const STATUSES = [
        'todo' => 'To do',
        'doing' => 'In progress',
        'done' => 'Done',
    ];

    protected $fillable = [
        'research_project_id', 'assignee_id', 'title', 'description', 'status', 'due_at',
    ];

    protected $casts = [
        'due_at' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ResearchProject::class, 'research_project_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}
