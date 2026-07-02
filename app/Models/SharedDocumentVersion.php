<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A point-in-time snapshot of a shared document's body.
 */
class SharedDocumentVersion extends Model
{
    protected $fillable = ['shared_document_id', 'body', 'saved_by'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(SharedDocument::class, 'shared_document_id');
    }

    public function savedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saved_by');
    }
}
