<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('expertise')->nullable()->after('bio');
            $table->json('country_experience')->nullable()->after('expertise');
            $table->json('languages')->nullable()->after('country_experience');
            $table->json('method_competencies')->nullable()->after('languages');
        });

        // Academic role vocabulary: the old default "member" becomes "researcher".
        DB::table('users')->where('role', 'member')->update(['role' => 'researcher']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'researcher')->update(['role' => 'member']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['expertise', 'country_experience', 'languages', 'method_competencies']);
        });
    }
};
