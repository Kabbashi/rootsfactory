<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DataItem extends Model
{
    /** Kind of qualitative datum, mapped to its human label. */
    public const KINDS = [
        'transcript' => 'Interview transcript',
        'focus_group' => 'Focus group',
        'field_note' => 'Field note',
        'observation' => 'Observation',
        'document' => 'Document',
        'media' => 'Media',
    ];

    protected $fillable = [
        'research_project_id', 'user_id', 'kind', 'title', 'content', 'path',
        'language', 'collected_at', 'source_meta',
    ];

    protected $casts = [
        'collected_at' => 'date',
        'source_meta' => 'array',
    ];

    public function kindLabel(): string
    {
        return self::KINDS[$this->kind] ?? $this->kind;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ResearchProject::class, 'research_project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function codes(): BelongsToMany
    {
        return $this->belongsToMany(Code::class, 'codings')
            ->withPivot('excerpt', 'user_id')
            ->withTimestamps();
    }

    public function codings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Coding::class);
    }
}
