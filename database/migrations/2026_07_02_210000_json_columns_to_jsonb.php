<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Postgres `json` has no equality operator, so any DISTINCT/GROUP BY touching a
 * table with a json column fails ("could not identify an equality operator for
 * type json") — which broke Filament's multiple relationship selects (they run
 * SELECT DISTINCT). `jsonb` supports equality, so convert every json column.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->jsonColumns('json') as $col) {
            DB::statement(
                "ALTER TABLE \"{$col->table_name}\" ALTER COLUMN \"{$col->column_name}\" "
                . "TYPE jsonb USING \"{$col->column_name}\"::jsonb"
            );
        }
    }

    public function down(): void
    {
        foreach ($this->jsonColumns('jsonb') as $col) {
            DB::statement(
                "ALTER TABLE \"{$col->table_name}\" ALTER COLUMN \"{$col->column_name}\" "
                . "TYPE json USING \"{$col->column_name}\"::json"
            );
        }
    }

    /** @return array<int, object{table_name: string, column_name: string}> */
    private function jsonColumns(string $type): array
    {
        return DB::select(
            'SELECT table_name, column_name FROM information_schema.columns '
            . "WHERE data_type = ? AND table_schema = 'public'",
            [$type]
        );
    }
};
