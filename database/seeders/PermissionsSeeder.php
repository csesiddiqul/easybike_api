<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'View Dashboard', 'slug' => 'view_dashboard', 'group' => 'Dashboard'],
            // User Management
            ['name' => 'Create User', 'slug' => 'create_user', 'group' => 'User Management'],
            ['name' => 'Edit User', 'slug' => 'edit_user', 'group' => 'User Management'],
            ['name' => 'Delete User', 'slug' => 'delete_user', 'group' => 'User Management'],
            ['name' => 'View User', 'slug' => 'view_user', 'group' => 'User Management'],

            // Role
            ['name' => 'Create Role', 'slug' => 'create_role', 'group' => 'Role'],
            ['name' => 'Edit Role', 'slug' => 'edit_role', 'group' => 'Role'],
            ['name' => 'Delete Role', 'slug' => 'delete_role', 'group' => 'Role'],
            ['name' => 'View Role', 'slug' => 'view_role', 'group' => 'Role'],
            ['name' => 'Assign Role', 'slug' => 'assign_role', 'group' => 'Role'],

            // Permission
            ['name' => 'View Permission', 'slug' => 'view_permission', 'group' => 'Permission'],
            ['name' => 'Create Permission', 'slug' => 'create_permission', 'group' => 'Permission'],
            ['name' => 'Edit Permission', 'slug' => 'edit_permission', 'group' => 'Permission'],
            ['name' => 'Delete Permission', 'slug' => 'delete_permission', 'group' => 'Permission'],
            ['name' => 'Assign Permission', 'slug' => 'assign_permission', 'group' => 'Permission'],

            // =====================
            // Fiscal Year Management
            // =====================
            ['name' => 'View Fiscal Year', 'slug' => 'view_fiscal_year', 'group' => 'Fiscal Year'],
            ['name' => 'Create Fiscal Year', 'slug' => 'create_fiscal_year', 'group' => 'Fiscal Year'],
            ['name' => 'Correct Fiscal Year', 'slug' => 'correct_fiscal_year', 'group' => 'Fiscal Year'],
            ['name' => 'Activate Fiscal Year', 'slug' => 'activate_fiscal_year', 'group' => 'Fiscal Year'],

            // =====================
            // Driver Management
            // =====================
            ['name' => 'View Driver', 'slug' => 'view_driver', 'group' => 'Driver'],
            ['name' => 'Create Driver', 'slug' => 'create_driver', 'group' => 'Driver'],
            ['name' => 'Edit Driver', 'slug' => 'edit_driver', 'group' => 'Driver'],
            ['name' => 'Deactivate Driver', 'slug' => 'deactivate_driver', 'group' => 'Driver'],
            ['name' => 'Assign Driver', 'slug' => 'assign_driver', 'group' => 'Driver'],
            ['name' => 'Create Owner', 'slug' => 'create_owner', 'group' => 'Owner Management'],
            ['name' => 'Edit Owner', 'slug' => 'edit_owner', 'group' => 'Owner Management'],
            ['name' => 'Delete Owner', 'slug' => 'delete_owner', 'group' => 'Owner Management'],
            ['name' => 'View Owner', 'slug' => 'view_owner', 'group' => 'Owner Management'],

            ['name' => 'Create vehicle', 'slug' => 'create_vehicle', 'group' => 'vehicle Management'],
            ['name' => 'Edit vehicle', 'slug' => 'edit_vehicle', 'group' => 'vehicle Management'],
            ['name' => 'Delete vehicle', 'slug' => 'delete_vehicle', 'group' => 'vehicle Management'],
            ['name' => 'View vehicle', 'slug' => 'view_vehicle', 'group' => 'vehicle Management'],

            // =====================
            // Driver Self Panel
            // =====================
            ['name' => 'View Own Profile', 'slug' => 'driver_self_profile', 'group' => 'Driver Self'],
            ['name' => 'View Own Licence', 'slug' => 'driver_self_licence', 'group' => 'Driver Self'],
            ['name' => 'View Own Payment History', 'slug' => 'driver_self_payment_history', 'group' => 'Driver Self'],
            ['name' => 'View Own Licence Renew History', 'slug' => 'driver_self_renew_history', 'group' => 'Driver Self'],
            ['name' => 'Make Licence Payment', 'slug' => 'driver_self_make_payment', 'group' => 'Driver Self'],

            ['name' => 'Owner Vehicles', 'slug' => 'view_owner_vehicle', 'group' => 'Owner vehicle Management'],








            // website setting
            ['name' => 'View Website Setting', 'slug' => 'view_website_setting', 'group' => 'Website Setting'],



        ];


        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        foreach ($permissions as $permission) {
            $permission['created_at'] = now();
            $permission['updated_at'] = now();
            DB::table('permissions')->insert($permission);
        }


        // Assign all permissions to the Admin role
        $adminRoleId = DB::table('roles')->where('name', 'Super Admin')->value('id');
        $permissions = DB::table('permissions')->pluck('id')->toArray();

        DB::table('role_permissions')->insert(
            array_map(function ($permissionId) use ($adminRoleId) {
                return ['role_id' => $adminRoleId, 'permission_id' => $permissionId, 'created_at' => now(), 'updated_at' => now()];
            }, $permissions)
        );

        // // Assign all permissions to the Admin role
        // $adminPermissions = Permission::all();
        // $RegisterPermissions = Permission::whereIn('group', ['Role', 'Permission'])->get();
        // $AssistantRegisterPermissions = Permission::whereIn('slug', ["assign_permission", "edit_permission"])->get();

        // $adminRole = Role::where('name', 'Super Admin')->first();
        // $RegisterRole = Role::where('name', 'Register')->first();
        // $AssistantRegisterRole = Role::where('name', 'Assistant Register')->first();

        // $adminRole->permissions()->sync($adminPermissions->pluck('id'));
        // $RegisterRole->permissions()->sync($RegisterPermissions->pluck('id'));
        // $AssistantRegisterRole->permissions()->sync($AssistantRegisterPermissions->pluck('id'));
    }
}
