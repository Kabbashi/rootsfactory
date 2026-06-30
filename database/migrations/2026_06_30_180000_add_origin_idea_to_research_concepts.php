<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Track which pool idea a research concept grew out of, so concepts born from
 * public ideas can carry the social layer (P7).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_concepts', function (Blueprint $table) {
            $table->foreignId('origin_idea_id')->nullable()->after('id')
                ->constrained('ideas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('research_concepts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('origin_idea_id');
        });
    }
};
