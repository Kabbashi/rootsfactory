<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Let a code be assigned directly to the ideas, concepts and research projects
 * it belongs to — an explicit link, alongside the data items it codes. The
 * code is the concrete side; the target (idea/concept/project) is polymorphic.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('codeables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('code_id')->constrained()->cascadeOnDelete();
            $table->morphs('codeable');
            $table->timestamps();
            $table->unique(
                ['code_id', 'codeable_type', 'codeable_id'],
                'codeables_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codeables');
    }
};
