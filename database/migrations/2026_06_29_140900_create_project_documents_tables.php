<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->longText('body')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->timestamps();
        });

        Schema::create('project_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version_no')->default(1);
            $table->longText('body')->nullable();
            $table->text('changelog')->nullable();
            $table->timestamps();

            $table->unique(['project_document_id', 'version_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_document_versions');
        Schema::dropIfExists('project_documents');
    }
};
