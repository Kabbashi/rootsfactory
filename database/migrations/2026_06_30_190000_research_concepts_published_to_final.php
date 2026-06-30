<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Research Concepts now end at "Final" instead of "Published" (publishing to
 * the public is the Publications layer's job). Migrate any existing rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('research_concepts')->where('status', 'published')->update(['status' => 'final']);
    }

    public function down(): void
    {
        DB::table('research_concepts')->where('status', 'final')->update(['status' => 'published']);
    }
};
