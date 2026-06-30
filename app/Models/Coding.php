<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One piece of coded evidence: an excerpt of a data item tagged with a code.
 */
class Coding extends Model
{
    protected $fillable = ['data_item_id', 'code_id', 'user_id', 'excerpt'];

    public function dataItem(): BelongsTo
    {
        return $this->belongsTo(DataItem::class);
    }

    public function code(): BelongsTo
    {
        return $this->belongsTo(Code::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
