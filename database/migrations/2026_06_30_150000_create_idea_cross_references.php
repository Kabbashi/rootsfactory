<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cross-references between ideas in the pool — the edges of the idea mindmap.
 * Stored one-way; rendered as undirected links in the map.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idea_cross_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idea_id')->constrained('ideas')->cascadeOnDelete();
            $table->foreignId('related_idea_id')->constrained('ideas')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['idea_id', 'related_idea_id'], 'idea_cross_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idea_cross_references');
    }
};
