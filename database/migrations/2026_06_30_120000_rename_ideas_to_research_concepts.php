<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Concept Notes" became "Research Concept": rename the table and the
 * attachment foreign key, and repoint the polymorphic comment type.
 * This frees the `ideas` name for the new Idea Pool.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('ideas', 'research_concepts');

        Schema::table('attachments', function (Blueprint $table) {
            $table->renameColumn('idea_id', 'research_concept_id');
        });

        DB::table('comments')
            ->where('commentable_type', 'App\\Models\\Idea')
            ->update(['commentable_type' => 'App\\Models\\ResearchConcept']);
    }

    public function down(): void
    {
        DB::table('comments')
            ->where('commentable_type', 'App\\Models\\ResearchConcept')
            ->update(['commentable_type' => 'App\\Models\\Idea']);

        Schema::table('attachments', function (Blueprint $table) {
            $table->renameColumn('research_concept_id', 'idea_id');
        });

        Schema::rename('research_concepts', 'ideas');
    }
};
