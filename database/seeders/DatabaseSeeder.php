<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Real accounts arrive via conceptnote SSO; keep a single fallback
        // login for local work, idempotently so re-seeding never collides.
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User'],
        );

        $this->call(StarterContentSeeder::class);
    }
}
