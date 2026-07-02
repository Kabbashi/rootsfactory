<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Task extends Model
{
    public const STATUSES = [
        'todo' => 'To do',
        'doing' => 'In progress',
        'done' => 'Done',
    ];

    /** Buckets (by subject type) for the Planner-style board. */
    public const BUCKETS = [
        'idea' => 'Idea',
        'concept' => 'Concept',
        'project' => 'Project',
    ];

    protected $fillable = [
        'research_project_id', 'assignee_id', 'created_by', 'taskable_type', 'taskable_id',
        'bucket_id', 'title', 'description', 'status', 'due_at',
    ];

    protected $casts = [
        'due_at' => 'date',
    ];

    /** Legacy direct project link (kept for existing project tasks). */
    public function project(): BelongsTo
    {
        return $this->belongsTo(ResearchProject::class, 'research_project_id');
    }

    /** What the task is about: an idea, a concept or a project. */
    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /** The member who set/delegated the task. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Members working on the task alongside the assignee. */
    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    /** The board column this task sits in. */
    public function bucket(): BelongsTo
    {
        return $this->belongsTo(Bucket::class);
    }

    /** Subject-type key ('idea'|'concept'|'project') derived from the subject. */
    public function bucketKey(): string
    {
        return match ($this->taskable_type) {
            Idea::class => 'idea',
            ResearchConcept::class => 'concept',
            ResearchProject::class => 'project',
            default => 'project',
        };
    }

    /** Human name of the subject (idea name / concept or project title). */
    public function subjectLabel(): ?string
    {
        $subject = $this->taskable;

        return $subject?->name ?? $subject?->title;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
