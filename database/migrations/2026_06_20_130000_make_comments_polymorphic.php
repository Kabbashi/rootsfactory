<?php

use App\Models\Idea;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Comments were hard-bound to ideas. Make them polymorphic so topics
     * (and future entities) can carry their own discussion thread.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->nullableMorphs('commentable');
        });

        // Backfill existing idea comments into the polymorphic columns.
        DB::table('comments')->whereNotNull('idea_id')->update([
            'commentable_id' => DB::raw('idea_id'),
            'commentable_type' => Idea::class,
        ]);

        Schema::table('comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('idea_id');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('idea_id')->nullable()->constrained()->cascadeOnDelete();
        });

        DB::table('comments')
            ->where('commentable_type', Idea::class)
            ->update(['idea_id' => DB::raw('commentable_id')]);

        Schema::table('comments', function (Blueprint $table) {
            $table->dropMorphs('commentable');
        });
    }
};
