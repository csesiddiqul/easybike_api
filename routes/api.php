<?php

use App\Http\Controllers\AlertMessageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FindAddressController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WebsiteSettingController;
use App\Http\Controllers\FiscalYearController;
use App\Http\Controllers\DriverController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Require Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/api/auth.php';
require __DIR__ . '/api/users.php';
require __DIR__ . '/api/authorization.php';


/*
|--------------------------------------------------------------------------
| Open Routes
|--------------------------------------------------------------------------
*/



/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

// Authenticate routes
Route::middleware('auth:sanctum')->group(function () {

    // TODO: 1
    // address
    Route::controller(FindAddressController::class)->group(function () {
        Route::get('/divisions', 'getDivisions');
        Route::get('/divisions/{division_id}/districts', 'getDistricts');
        Route::get('/districts/{district_id}/upazilas', 'getUpazilas');
        Route::get('/upazilas/{upazila_id}/unions', 'getUnions');
    });
    // dashboard
    Route::get('dashboard', [DashboardController::class, 'dashboard']);

    // TODO: 3
    // stock
    Route::apiResource('stocks', StockController::class);

    //Owner
    // Route::apiResource('owners', OwnerController::class);
    Route::apiResource('owners', OwnerController::class)->middleware([
        'index' => 'permissions:view_owner',
        'show' => 'permissions:view_owner',
        'store' => 'permissions:create_owner',
        'update' => 'permissions:edit_owner',
        'destroy' => 'permissions:delete_owner',
    ]);
    //Vehicle

    Route::apiResource('vehicles', VehicleController::class)->middleware([
        'index' => 'permissions:view_vehicle',
        'show' => 'permissions:view_vehicle',
        'store' => 'permissions:create_vehicle',
        'update' => 'permissions:edit_vehicle',
        'destroy' => 'permissions:delete_vehicle',
    ]);

    Route::get('owner-select-options', [OwnerController::class, 'ownerSelectOptions']);
    Route::get('driver-select-options', [DriverController::class, 'driverSelectOptions']);



    Route::post('stock-reports', [StockController::class, 'stockreport']);

    // alert messages
    Route::get('get-alert-messages', [AlertMessageController::class, 'index']);



    // =========== Apurbo Route ===========//
    Route::apiResource('fiscal-years', FiscalYearController::class)->only(['index', 'store', 'show']);
    Route::patch('fiscal-years/{fiscalYear}/activate', [FiscalYearController::class, 'activate']);
    Route::patch('fiscal-years/{fiscalYear}/correct', [FiscalYearController::class, 'correct']);
    Route::apiResource('drivers', DriverController::class);
});


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
// website settings
Route::prefix("website-settings")->controller(WebsiteSettingController::class)->group(function () {
    Route::get('/', 'index');
    Route::patch('/', 'storeOrUpdate');
});
