<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tasks become a shared, delegatable to-do across ideas, concepts and
 * projects. A task now records who set it (created_by), what it is about
 * (taskable), and who else works on it (task_user). The old project-only
 * tasks are backfilled onto the polymorphic link.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('assignee_id')
                ->constrained('users')->nullOnDelete();
            $table->string('taskable_type')->nullable()->after('created_by');
            $table->unsignedBigInteger('taskable_id')->nullable()->after('taskable_type');
            $table->index(['taskable_type', 'taskable_id']);
        });

        // Existing project tasks -> polymorphic link to their project.
        DB::table('tasks')->whereNotNull('research_project_id')->update([
            'taskable_type' => \App\Models\ResearchProject::class,
            'taskable_id' => DB::raw('research_project_id'),
        ]);

        // Project is no longer mandatory now that tasks can hang off anything.
        DB::statement('ALTER TABLE tasks ALTER COLUMN research_project_id DROP NOT NULL');

        // Collaborators — the people working on a task alongside the assignee.
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['task_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_user');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropIndex(['taskable_type', 'taskable_id']);
            $table->dropColumn(['taskable_type', 'taskable_id']);
        });
    }
};
