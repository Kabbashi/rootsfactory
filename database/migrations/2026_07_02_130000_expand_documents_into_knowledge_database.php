<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Grow the Library into a full Knowledge Database entry: rich bibliographic
 * metadata, an AI-drafted abstract, a task with an assignee, and polymorphic
 * cross-references to other library entries, ideas, concepts and projects.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('type')->nullable()->after('kind');
            $table->string('authors')->nullable()->after('type');
            $table->string('institution')->nullable()->after('authors');
            $table->string('subtitle')->nullable()->after('institution');
            $table->string('published_by')->nullable()->after('subtitle');
            $table->string('year', 20)->nullable()->after('published_by');
            $table->string('pages', 50)->nullable()->after('year');
            $table->string('website')->nullable()->after('pages');
            $table->text('table_of_contents')->nullable()->after('website');
            $table->text('abstract')->nullable()->after('table_of_contents');
            $table->text('task')->nullable()->after('abstract');
            $table->foreignId('assigned_to')->nullable()->after('task')
                ->constrained('users')->nullOnDelete();
        });

        // Cross-references: a document links to other library entries, ideas,
        // concepts or projects. The document is the concrete side; the target
        // is polymorphic (linkable).
        Schema::create('document_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->morphs('linkable');
            $table->timestamps();
            $table->unique(
                ['document_id', 'linkable_type', 'linkable_id'],
                'document_links_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_links');

        Schema::table('documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropColumn([
                'type', 'authors', 'institution', 'subtitle', 'published_by',
                'year', 'pages', 'website', 'table_of_contents', 'abstract', 'task',
            ]);
        });
    }
};
