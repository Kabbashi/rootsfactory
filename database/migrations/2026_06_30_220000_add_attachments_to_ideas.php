<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Let ideas carry document attachments (PDF, Word, …) alongside the optional
 * image used for the AI core-statement feature.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            // jsonb (not json) so Postgres can DISTINCT the column — Filament's
            // self-referencing crossReferences select does select distinct ideas.*
            $table->jsonb('attachments')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};
