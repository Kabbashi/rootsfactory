<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_topic', function (Blueprint $table) {
            $table->foreignId('research_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->primary(['research_project_id', 'topic_id']);
        });

        Schema::create('project_region', function (Blueprint $table) {
            $table->foreignId('research_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->primary(['research_project_id', 'region_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_topic');
        Schema::dropIfExists('project_region');
    }
};
