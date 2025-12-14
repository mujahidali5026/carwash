<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // DB::table('users')->insert([
        //     'name' => 'Admin',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('password'),
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);
        User::updateOrCreate([
            'email' => 'admin@carwash.com'
        ], [
            'name' => 'Admin',
            'email' => 'admin@carwash.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::updateOrCreate([
            'email' => 'staff@carwash.com'
        ], [
            'name' => 'Staff',
            'email' => 'staff@carwash.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
    }
}
