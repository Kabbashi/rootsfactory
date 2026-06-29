<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reference extends Model
{
    protected $fillable = [
        'research_project_id', 'title', 'authors', 'year', 'source', 'url',
        'doi', 'citation_key', 'notes',
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ResearchProject::class, 'research_project_id');
    }
}
