<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            // working_paper | research_paper | policy_brief | strategy_paper | report | critical_column | essay
            $table->string('type')->default('working_paper');
            // draft | internal_review | peer_review | revision | copy_edit | approved | published | archived
            $table->string('status')->default('draft');
            $table->string('language', 8)->default('en');
            $table->text('abstract')->nullable();
            $table->longText('body')->nullable();
            $table->string('doi')->nullable();
            $table->text('citation')->nullable();
            $table->unsignedInteger('downloads')->default(0);
            // Points at the active publication_versions row (no FK: avoids a
            // circular dependency with that table).
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
