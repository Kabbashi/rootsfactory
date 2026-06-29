<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProjectDocument extends Model
{
    protected $fillable = [
        'research_project_id', 'created_by', 'title', 'body', 'version_no',
    ];

    protected static function booted(): void
    {
        // Snapshot a version whenever the body changes — the async, versioned
        // collaborative editor keeps a browsable history.
        static::updating(function (ProjectDocument $document): void {
            if ($document->isDirty('body')) {
                $document->version_no = (int) $document->version_no + 1;
                $document->versions()->create([
                    'created_by' => auth()->id(),
                    'version_no' => $document->version_no,
                    'body' => $document->body,
                ]);
            }
        });

        static::created(function (ProjectDocument $document): void {
            $document->versions()->create([
                'created_by' => $document->created_by,
                'version_no' => $document->version_no ?: 1,
                'body' => $document->body,
            ]);
        });

        static::deleting(fn (ProjectDocument $document) => $document->comments()->delete());
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ResearchProject::class, 'research_project_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProjectDocumentVersion::class)->orderByDesc('version_no');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
