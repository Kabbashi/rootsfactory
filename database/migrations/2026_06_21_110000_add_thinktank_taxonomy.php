<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Geographic taxonomy — the second axis alongside topics, very natural
        // for development-cooperation work (Sahel, East Africa, …).
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('ideas', function (Blueprint $table) {
            // What kind of publication this is — drives the badge on cards.
            $table->string('type')->default('brief')->after('slug');
            $table->foreignId('region_id')->nullable()->after('topic_id')->constrained()->nullOnDelete();
        });

        // Public author profiles.
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('title')->nullable()->after('slug');   // role / affiliation
            $table->text('bio')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('region_id');
            $table->dropColumn('type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['slug', 'title', 'bio']);
        });

        Schema::dropIfExists('regions');
    }
};
