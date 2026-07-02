<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Publications gain classification (topic, country/region), an attached
 * document to open/download, and where they were published (default: R2N).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->foreignId('topic_id')->nullable()->after('research_project_id')->constrained()->nullOnDelete();
            $table->foreignId('region_id')->nullable()->after('topic_id')->constrained()->nullOnDelete();
            $table->string('path')->nullable()->after('body');
            $table->string('original_name')->nullable()->after('path');
            $table->string('mime')->nullable()->after('original_name');
            $table->unsignedBigInteger('size')->nullable()->after('mime');
            $table->json('published_in')->nullable()->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topic_id');
            $table->dropConstrainedForeignId('region_id');
            $table->dropColumn(['path', 'original_name', 'mime', 'size', 'published_in']);
        });
    }
};
