<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Turn categories into one shared, hierarchical taxonomy used across the
 * Idea Pool, Research Concepts, Research Projects and Codes. Categories gain
 * a parent (self-reference) and an explicit sort order, and a polymorphic
 * pivot lets any entity carry many categories.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')
                ->constrained('categories')->nullOnDelete();
            $table->unsignedInteger('sort')->default(0)->after('description');
        });

        Schema::create('categorizables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->morphs('categorizable');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->unique(
                ['category_id', 'categorizable_id', 'categorizable_type'],
                'categorizables_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorizables');

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('sort');
        });
    }
};
