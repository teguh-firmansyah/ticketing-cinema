<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@ticketing.test',
            'password' => Hash::make("password"),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'User',
            'email' => 'user@ticketing.test',
            'password' => Hash::make("password"),
            'role' => 'user'
        ]);

        User::create([
            'name' => 'Officer',
            'email' => 'officer@ticketing.test',
            'password' => Hash::make("password"),
            'role' => 'field_officer'
        ]);

        $this->call([
            // Core
            SettingSeeder::class,
            PageSeeder::class,
            AboutSectionSeeder::class,

            // Event ticketing
            CategorySeeder::class,
            EventSeeder::class,
            BlogSeeder::class,
            FaqSeeder::class,

            // Cinema
            CinemaSeeder::class,
            MovieSeeder::class,
            ShowtimeSeeder::class,
        ]);
    }
}
