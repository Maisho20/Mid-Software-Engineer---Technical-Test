<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'full_name' => 'user 1',
            'email' => 'example1@mail.net',
            'password' => bcrypt('password')
        ]);
        User::create([
            'full_name' => 'User 2',
            'email' => 'example2@mail.net',
            'password' => bcrypt('password'),
        ]);
    }
}
