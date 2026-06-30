<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Postgres full-text search: a generated tsvector column (title weighted above
 * body) with a GIN index on each searchable entity. No extra dependency —
 * queried with websearch_to_tsquery from the workspace Search page.
 */
return new class extends Migration
{
    /** @var array<string, string> table => weighted tsvector expression */
    private array $vectors = [
        'ideas' => "setweight(to_tsvector('english', coalesce(name,'')), 'A') || setweight(to_tsvector('english', coalesce(core_statement,'') || ' ' || coalesce(description,'')), 'B')",
        'research_concepts' => "setweight(to_tsvector('english', coalesce(title,'')), 'A') || setweight(to_tsvector('english', coalesce(body,'')), 'B')",
        'research_projects' => "setweight(to_tsvector('english', coalesce(title,'')), 'A') || setweight(to_tsvector('english', coalesce(summary,'') || ' ' || coalesce(objectives,'') || ' ' || coalesce(methodology,'') || ' ' || coalesce(findings,'')), 'B')",
        'documents' => "setweight(to_tsvector('english', coalesce(title,'') || ' ' || coalesce(original_name,'')), 'A') || setweight(to_tsvector('english', coalesce(description,'')), 'B')",
        'publications' => "setweight(to_tsvector('english', coalesce(title,'')), 'A') || setweight(to_tsvector('english', coalesce(abstract,'') || ' ' || coalesce(body,'')), 'B')",
    ];

    public function up(): void
    {
        foreach ($this->vectors as $table => $expr) {
            DB::statement("ALTER TABLE {$table} ADD COLUMN search_vector tsvector GENERATED ALWAYS AS ({$expr}) STORED");
            DB::statement("CREATE INDEX {$table}_search_idx ON {$table} USING GIN (search_vector)");
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->vectors) as $table) {
            DB::statement("DROP INDEX IF EXISTS {$table}_search_idx");
            DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS search_vector");
        }
    }
};
