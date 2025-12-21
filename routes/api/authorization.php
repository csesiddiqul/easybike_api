<?php

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authorization Routes
|--------------------------------------------------------------------------
*/

// admin routes: if user role is admin then can access admin routes 
Route::middleware(['auth:sanctum'])->group(function(){
    
    // role routes
    Route::prefix("roles")->group(function(){
        Route::get('/', [RoleController::class, 'index'])->middleware("permissions:view_role");
        Route::get('/{id}', [RoleController::class,'show'])->middleware("permissions:edit_role");   
        Route::post('/', [RoleController::class,'store'])->middleware("permissions:create_role");
        Route::put('/{id}', [RoleController::class, 'update'])->middleware("permissions:edit_role");
        Route::delete('/{id}', [RoleController::class, 'destroy'])->middleware("permissions:delete_role");
        Route::put('/assign-role-to-user/{userId}', [RoleController::class, 'assignRoleToUser'])->middleware("permissions:assign_role");
        Route::get('/show-role-permissions/{id}', [RoleController::class, 'showRoleWithPermissions'])->middleware("permissions:view_role");
    });

    // permission routes
    Route::prefix("permissions")->group(function(){
        Route::get('/', [PermissionController::class, 'index'])->middleware("permissions:view_permission");
        Route::post('/assign-permissions-to-role', [PermissionController::class, 'assignPermissionsToRole'])->middleware("permissions:assign_permission");
        Route::put('/update-permissions-to-role', [PermissionController::class, 'updatePermissionsToRole'])->middleware("permissions:edit_permission");
    });

});
