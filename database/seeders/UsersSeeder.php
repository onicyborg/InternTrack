<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default Admin Company account
        $email = 'admin@company.test';

        User::updateOrCreate(
            ['email' => $email],
            [
                'role' => 'company_admin',
                'password' => 'admin12345', // Will be hashed by Eloquent cast
                'is_active' => true,
                'remember_token' => Str::random(60),
            ]
        );
    }
}
