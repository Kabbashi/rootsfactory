<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            // draft → in_discussion → published
            $table->string('status')->default('draft');
            $table->boolean('pinned')->default(false);
            $table->timestamps();

            $table->index(['status', 'pinned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ideas');
    }
};
