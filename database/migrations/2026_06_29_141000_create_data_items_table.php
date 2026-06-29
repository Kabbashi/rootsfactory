<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // transcript | focus_group | field_note | observation | document | media
            $table->string('kind')->default('transcript');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('path')->nullable();
            $table->string('language', 8)->default('en');
            $table->date('collected_at')->nullable();
            $table->json('source_meta')->nullable();
            $table->timestamps();

            $table->index(['research_project_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_items');
    }
};
