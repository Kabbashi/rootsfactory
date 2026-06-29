<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('authors')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('source')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('doi')->nullable();
            $table->string('citation_key')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('references');
    }
};
