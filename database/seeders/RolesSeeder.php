<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed roles
        $roles = [
            ['name' => 'Super Admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Owner', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Driver', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert($role);
        }
    }
}
