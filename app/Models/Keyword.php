<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A shared, reusable keyword. The same vocabulary is autocompleted across the
 * Idea Pool and Research Concepts via the HasKeywords trait / keywordables.
 */
class Keyword extends Model
{
    protected $fillable = ['name'];
}
