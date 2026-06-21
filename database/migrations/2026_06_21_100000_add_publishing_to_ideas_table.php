<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            // Pretty, stable URL for the public publication view.
            $table->string('slug')->nullable()->unique()->after('title');
            // When the idea first went public — drives ordering and the byline.
            $table->timestamp('published_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->dropColumn(['slug', 'published_at']);
        });
    }
};
