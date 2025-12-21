<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an admin user with Admin role
        $adminRoleId = DB::table('roles')->where('name', 'Super Admin')->value('id');

        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '01905992833',
            'user_name' => '019059928331',
            'role_id' => $adminRoleId,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
