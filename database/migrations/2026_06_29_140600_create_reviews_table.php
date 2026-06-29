<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            // internal | peer
            $table->string('stage')->default('internal');
            // pending | in_progress | done
            $table->string('status')->default('pending');
            // accept | minor_revisions | major_revisions | reject
            $table->string('recommendation')->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();

            $table->index(['publication_id', 'stage', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
