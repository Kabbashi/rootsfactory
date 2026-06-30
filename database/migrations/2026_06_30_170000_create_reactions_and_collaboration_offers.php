<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The social layer for public ideas (and, later, public concepts): emoji
 * reactions and offers to collaborate. Both are polymorphic.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('reactable');
            $table->string('emoji', 16);
            $table->timestamps();
            $table->unique(
                ['user_id', 'reactable_type', 'reactable_id', 'emoji'],
                'reactions_unique'
            );
        });

        Schema::create('collaboration_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('offerable');
            $table->text('message')->nullable();
            $table->timestamps();
            $table->unique(
                ['user_id', 'offerable_type', 'offerable_id'],
                'collaboration_offers_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaboration_offers');
        Schema::dropIfExists('reactions');
    }
};
