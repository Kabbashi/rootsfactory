<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A document the editorial team drafts together. Editing is async: each save
 * records who touched it and snapshots a version for history and restore.
 */
class SharedDocument extends Model
{
    protected $fillable = ['title', 'body', 'created_by', 'updated_by'];

    protected static function booted(): void
    {
        static::creating(fn (SharedDocument $doc) => $doc->created_by ??= auth()->id());

        static::saving(function (SharedDocument $doc): void {
            if (auth()->check()) {
                $doc->updated_by = auth()->id();
            }
        });

        // Snapshot the initial content and every later change to the body.
        static::created(fn (SharedDocument $doc) => $doc->snapshot());

        static::updated(function (SharedDocument $doc): void {
            if ($doc->wasChanged('body')) {
                $doc->snapshot();
            }
        });

        static::deleting(fn (SharedDocument $doc) => $doc->comments()->delete());
    }

    /** Store the current body as a version. */
    public function snapshot(): void
    {
        $this->versions()->create([
            'body' => $this->body,
            'saved_by' => auth()->id(),
        ]);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SharedDocumentVersion::class)->latest();
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
