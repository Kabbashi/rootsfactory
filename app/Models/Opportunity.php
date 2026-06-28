<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    /** What kind of opportunity this is, mapped to its human label. */
    public const TYPES = [
        'grant' => 'Grant',
        'tender' => 'Tender',
        'partnership' => 'Partnership',
    ];

    /** Types shown in the Funding Center vs the Opportunity Center. */
    public const FUNDING_TYPES = ['grant'];

    public const OPPORTUNITY_TYPES = ['tender', 'partnership'];

    public const STATUSES = [
        'open' => 'Open',
        'closed' => 'Closed',
        'draft' => 'Draft',
    ];

    protected $fillable = [
        'type', 'title', 'organisation', 'description', 'amount',
        'deadline', 'url', 'status', 'ai_suggested', 'topic_id', 'region_id', 'user_id',
    ];

    protected $casts = [
        'deadline' => 'date',
        'ai_suggested' => 'boolean',
    ];

    /** Grants — the Funding Center. */
    public function scopeFunding(Builder $query): Builder
    {
        return $query->whereIn('type', self::FUNDING_TYPES);
    }

    /** Tenders and partnerships — the Opportunity Center. */
    public function scopeOpportunities(Builder $query): Builder
    {
        return $query->whereIn('type', self::OPPORTUNITY_TYPES);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
