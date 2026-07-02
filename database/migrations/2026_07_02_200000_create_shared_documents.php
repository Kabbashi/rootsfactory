<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A shared editor for the Editorial Office: documents the whole team drafts
 * together (async, not real-time). Every meaningful save snapshots a version,
 * so history is browsable and restorable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('body')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('shared_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shared_document_id')->constrained()->cascadeOnDelete();
            $table->longText('body')->nullable();
            $table->foreignId('saved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_document_versions');
        Schema::dropIfExists('shared_documents');
    }
};
