<?php

namespace Database\Seeders;

use App\Enums\Role;
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
        // One account per role for local dev login testing (password: "password").
        User::factory()->create(['name' => 'Admin', 'email' => 'admin@hvu.test', 'role' => Role::Admin]);
        User::factory()->create(['name' => 'Staff', 'email' => 'staff@hvu.test', 'role' => Role::Staff]);
        User::factory()->create(['name' => 'Technician', 'email' => 'tech@hvu.test', 'role' => Role::Technician]);
        User::factory()->create(['name' => 'Customer', 'email' => 'customer@hvu.test', 'role' => Role::Customer]);
    }
}
