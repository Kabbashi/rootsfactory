<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $fillable = ['research_concept_id', 'title', 'path', 'kind'];

    public function researchConcept(): BelongsTo
    {
        return $this->belongsTo(ResearchConcept::class);
    }
}
