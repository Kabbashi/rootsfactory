<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Planner-style buckets for the task board. Three system buckets (Idea,
 * Concept, Project) exist by default and are tied to the subject type; members
 * can add their own buckets too. Tasks get an optional bucket.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buckets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // idea | concept | project for the three system buckets; null for custom.
            $table->string('system_type')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('buckets')->insert([
            ['name' => 'Idea', 'system_type' => 'idea', 'sort' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Concept', 'system_type' => 'concept', 'sort' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Project', 'system_type' => 'project', 'sort' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('bucket_id')->nullable()->after('taskable_id')
                ->constrained('buckets')->nullOnDelete();
        });

        // Backfill: put existing tasks in the system bucket matching their subject.
        $map = [
            \App\Models\Idea::class => 'idea',
            \App\Models\ResearchConcept::class => 'concept',
            \App\Models\ResearchProject::class => 'project',
        ];
        foreach ($map as $class => $type) {
            $bucketId = DB::table('buckets')->where('system_type', $type)->value('id');
            DB::table('tasks')->where('taskable_type', $class)->update(['bucket_id' => $bucketId]);
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bucket_id');
        });

        Schema::dropIfExists('buckets');
    }
};
