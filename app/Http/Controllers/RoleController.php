<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Facades\HandleResponseFacade as Response;
use App\Http\Requests\RoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    // assign role to user
    public function assignRoleToUser(Request $request, $userId)
    {
        try {
            $request->validate([
                'role_id' => 'required|exists:roles,id',
            ]);

            $user = User::find($userId);
            if (!$user) {
                return Response::sendError("Error", "User not found", 404);
            }
            $user->role_id = $request->role_id;
            $user->save();
            
            return Response::sendResponse('Role assigned successfully',$user);
        } catch (\Exception $e) {
            return Response::sendError("Error",$e->getMessage());
        }
    }
    // All roles
    public function index()
    {
        try {
            $roles = Role::index()->with("permissions")->get();
            return Response::sendResponse('Successful', $roles);
        } catch (\Exception $e) {
            return Response::sendError("Error",$e->getMessage());
    
        }
    }

    // show role with permissions
    public function showRoleWithPermissions($id)
    {
        try {
            // Retrieve the role with its associated permissions
            $role = Role::with("permissions")->find($id);
            
            // If the role is not found, you can handle it separately
            if (!$role) {
                return Response::sendError("Error", "Role not found", 404);
            }

            return Response::sendResponse('Successful', $role);
        } catch (\Exception $e) {
            return Response::sendError("Error",$e->getMessage());
    
        }
    }

    // Create role
    public function store(RoleRequest $request)
    {
        try {
            // Create the role with the name from the request
            $role = Role::create([
                'name' => $request->name,
            ]);
    
            // Attach permissions to the role
            $role->permissions()->attach($request->permissions);
    
            // Return a success response
            return Response::sendResponse('Role created successfully', $role);
        } catch (\Exception $e) {
            // Return an error response
            return Response::sendError("Error", $e->getMessage());
        }
    }
    

    //Find role
    public function show($id)
    {
        $role = Role::findOrFail($id);
        return Response::sendResponse('Role show successfully',$role);
    }

    // Update role
    public function update(UpdateRoleRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $role = Role::find($id);
            if (!$role) {
                return Response::sendError("Error", "Role not found", 404);
            }

            $role->update(['name' => $request->name]);

            
            // Detach existing permissions
            $role->permissions()->detach();

            // Attach the new permissions to the role
            $role->permissions()->attach($request->permissions);
            DB::commit();
            return Response::sendResponse('Role updated successfully', $role);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::sendError("Error", $e->getMessage());
        }
    }
    
    // Delete role
    public function destroy($id)
    {
        try {
            $role = Role::find($id);
            if (!$role) {
                return Response::sendError("Error", "Role not found", 404);
            }
            
            // Check if the role is associated with auth user
            if (auth()->user()->id == $id || $id == 1) {
                return Response::sendError('error', 'You cannot delete your own role or admin role.', 400);
            }
        
            // Additional check if the role is assigned to any user (assuming you have a User model and a pivot table for user roles)
            $usersWithRoleCount = User::whereHas('role', function ($query) use ($id) {
                $query->where('role_id', $id);
            })->count();
            if ($usersWithRoleCount > 0) {
                return Response::sendError('error', 'Role is assigned to users. Remove the role from users before deleting.', 400);
            }
            DB::beginTransaction();
            // Detach the role  from the pivot table
            $role->permissions()->detach();
            // Delete the role
            Role::destroy($id);
            DB::commit();
            return Response::sendResponse("Role deleted successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            $errorCode = $e->getCode();
            $statusCode = ($errorCode >= 400 && $errorCode < 600) ? $errorCode : 500;
            return Response::sendError("Error", $e->getMessage(), $statusCode);
        }
    }
}