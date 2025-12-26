<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Facades\HandleResponseFacade as Response;
use App\Http\Requests\RolePermissionRequest;
use App\Models\Permission;
use App\Models\Role;
use Exception;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    // all permissions
    public function index()
    {
        try{
            $data = Permission::all();
            return Response::sendResponse('Permissions retrieved successfully.', $data);
        }
        catch(Exception $e){
            return Response::sendError('Error', $e->getMessage());
        }  
    }

    // Assign multiple permissions to the role
    public function assignPermissionsToRole(RolePermissionRequest $request)
    {
        try {
            DB::beginTransaction();
            // Check if role id is not 1
            if ($request->role_id == 1) {
                return Response::sendError('Error', 'Admin role can access all permissions and already assign.');
            }
            $role = Role::find($request->role_id);
            if (!$role) {
                return Response::sendError("Error", "Role not found", 404);
            }
            $permissionIds = $request->permission_id;

            // Check if any of the permissions are already assigned to the role
            $existingPermissions = $role->permissions()->whereIn('permissions.id', $permissionIds)->get();
            // $existingPermissions = $role->permissions()
            //     ->wherePivot('role_id', $role->id) // Filter by the role_id
            //     ->whereIn('permissions.id', $permissionIds)
            //     ->get();
            // if ($existingPermissions->isNotEmpty()) {
            //     return Response::sendError('error', 'Some permissions {{existingPermissions.id}} are already assigned to the role.', 400);
            // }

            if ($existingPermissions->isNotEmpty()) {
                $existingPermissionIds = $existingPermissions->pluck('name')->implode(', '); // Get IDs as a string
                return Response::sendError('error', "Some permissions ( $existingPermissionIds ) are already assigned to the role.", 400);
            }
            
            // Attach the permissions to the role
            $role->permissions()->attach($permissionIds);
            DB::commit();
            return Response::sendResponse('Permissions assigned to role successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::sendError('Error', $e->getMessage());
        }
    }


    public function updatePermissionsToRole(RolePermissionRequest $request)
    {
        try {
            DB::beginTransaction();
            // Check if role id is not 1
            if ($request->role_id == 1) {
                return Response::sendError('Error', 'Admin role cannot be updated.');
            }

            $role = Role::findOrFail($request->role_id);
            $permissionIds = $request->permission_id;

            // Detach existing permissions
            $role->permissions()->detach();

            // Attach the new permissions to the role
            $role->permissions()->attach($permissionIds);
            DB::commit();
            return Response::sendResponse('Permissions assigned to role successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::sendError('Error', $e->getMessage());
        }
    }


}