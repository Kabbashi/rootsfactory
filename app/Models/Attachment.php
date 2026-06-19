<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $fillable = ['idea_id', 'title', 'path', 'kind'];

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }
}
