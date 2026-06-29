<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    public const STAGES = [
        'internal' => 'Internal review',
        'peer' => 'Peer review',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In progress',
        'done' => 'Done',
    ];

    public const RECOMMENDATIONS = [
        'accept' => 'Accept',
        'minor_revisions' => 'Minor revisions',
        'major_revisions' => 'Major revisions',
        'reject' => 'Reject',
    ];

    protected $fillable = [
        'publication_id', 'reviewer_id', 'stage', 'status', 'recommendation', 'comments', 'due_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
