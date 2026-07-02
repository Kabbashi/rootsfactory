<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Members can link their professional profiles. Optional, shown on the
 * member's profile inside the network (and on the public profile if they
 * have opted in).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('linkedin')->nullable()->after('method_competencies');
            $table->string('instagram')->nullable()->after('linkedin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['linkedin', 'instagram']);
        });
    }
};
