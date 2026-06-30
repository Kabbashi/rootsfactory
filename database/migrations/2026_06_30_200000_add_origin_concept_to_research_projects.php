<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Link a research project back to the research concept it grew from, so a
 * concept's text can flow into a project automatically when it goes final,
 * and we never create the same project twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_projects', function (Blueprint $table) {
            $table->foreignId('origin_concept_id')->nullable()->after('id')
                ->constrained('research_concepts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('research_projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('origin_concept_id');
        });
    }
};
