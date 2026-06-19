<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // member | editor | admin
            $table->string('role')->default('member')->after('email');
            // OIDC subject from the conceptnote SSO (filled once SSO is wired up).
            $table->string('sso_subject')->nullable()->unique()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'sso_subject']);
        });
    }
};
