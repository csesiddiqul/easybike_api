<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Facades\HandleResponseFacade as Response;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // User role permissions
    public function userRolePermissions()
    {
        try {
            $auth = Auth::user()->load("role.permissions");
            $data = new UserResource($auth);
            return Response::sendResponse('User retrieved successfully.', $data);
        } catch (Exception $e) {
            return Response::sendError('Error', $e->getMessage());
        }
    }

    // All user
    public function index(Request $request)
    {
        try {
            $users = User::query()
                ->with("role")
                ->whereAny(
                    [
                        'name',
                        'email'
                    ],
                    'LIKE',
                    "%$request->searchText%"
                )->paginate($request->per_page ?? 10);
            return UserResource::collection($users);
        } catch (Exception $e) {
            return Response::sendError('Error', $e->getMessage());
        }
    }


    // user create
    public function store(UserCreateRequest $request)
    {
        try {
            User::create([
                'name' => $request->name,
                'status' => $request->status,
                'role_id' => $request->role_id,
                'email' => trim($request->email),
                'password' => Hash::make("password"),
            ]);

            return Response::sendResponse("User created successfully");
        } catch (\Exception $e) {
            return Response::sendError('Error', $e->getMessage(), $e->getCode());
        }
    }


    public function update(UserUpdateRequest $request)
    {
        try {
            $user = User::findOrFail($request->id);

            // Prevent updating the user with role_id 1 (Admin) or the Admin user's role or the first user in the database
            if ($user->id == 1) {
                return Response::sendError('Unauthorized action');
            }


            $userData = [
                'name' => $request->name,
                'status' => $request->status,
                'role_id' => $request->role_id,
                'email' => trim($request->email),
            ];

            // Update the user
            $user->update($userData);

            return Response::sendResponse("User updated successfully");
        } catch (\Exception $e) {
            return Response::sendError('Error', $e->getMessage(), $e->getCode());
        }
    }


    // delete user
    public function destroy($id)
    {
        try {
            $user = User::find($id);

            // Prevent updating the user with role_id 1 (Admin) or the Admin user's role or the first user in the database
            if ($user->role_id == 1 || $user->roles->pluck('name')->contains('Super Admin') || $id == 1) {
                return Response::sendError('Unauthorized action');
            }
            User::destroy($id);
            return Response::sendResponse('User deleted successfully.');
        } catch (\Exception $e) {
            return Response::sendError('Error', $e->getMessage());
        }
    }


    // Auth user
    public function authUser()
    {
        try {
            $user = Auth::user();
            return Response::sendResponse('User retrieved successfully.', $user);
        } catch (Exception $e) {
            return Response::sendError('Error', $e->getMessage());
        }
    }

    // Find user
    public function show($id)
    {
        $user = User::findOrFail($id);
        return Response::sendResponse('User retrieved successfully.', $user);
    }





    // up


}
