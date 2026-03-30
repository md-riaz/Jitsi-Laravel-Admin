<?php

namespace Database\Seeders;

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
        $this->call([
            TyroRolesAndPrivilegesSeeder::class,
        ]);

        if (config('app.env') !== 'production' && env('ENABLE_DEMO_SEEDERS', false)) {
            $this->call([
                DemoDataSeeder::class,
            ]);
        }
    }
}
