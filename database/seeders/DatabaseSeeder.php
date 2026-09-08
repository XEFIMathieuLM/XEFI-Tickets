<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Xefi\LaravelOSDD\SeederRegistry;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database, then every seeder an OSDD layer registered
     * through loadSeeders(), so `migrate:fresh --seed` covers the layers too.
     */
    public function run(SeederRegistry $registry): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        foreach ($registry->seeders() as $seeder) {
            $this->call($seeder);
        }
    }
}
