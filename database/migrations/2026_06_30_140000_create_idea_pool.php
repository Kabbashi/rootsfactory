<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Idea Pool: a brainstorming layer that sits before Research Concepts.
 * Each idea has a short name, a central "core statement" and a description,
 * can be public (visible to the whole network) or personal (owner only),
 * and carries shared categories and keywords. Mindmap, image-to-AI,
 * cross-references and reactions are added later (P2).
 *
 * Keywords are a shared, reusable vocabulary (own table) so they can be
 * autocompleted across ideas and concepts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('core_statement')->nullable();
            $table->text('description')->nullable();
            $table->string('visibility')->default('personal'); // personal | public
            $table->timestamps();
        });

        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('keywordables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_id')->constrained()->cascadeOnDelete();
            $table->morphs('keywordable');
            $table->timestamps();
            $table->unique(
                ['keyword_id', 'keywordable_id', 'keywordable_type'],
                'keywordables_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keywordables');
        Schema::dropIfExists('keywords');
        Schema::dropIfExists('ideas');
    }
};
