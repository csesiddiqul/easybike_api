<?php

use App\Http\Controllers\AlertMessageController;
use App\Http\Controllers\API\VehiclePaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FindAddressController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\AppSettingController;

use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WebsiteSettingController;
use App\Http\Controllers\FiscalYearController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VehicleLicenseController;
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

    Route::apiResource('owner-vehicles', VehicleController::class)->middleware([
        'index' => 'permissions:view_owner_vehicle',
        'show' => 'permissions:view_owner_vehicle',
        'store' => 'permissions:create_owner_vehicle',
        'update' => 'permissions:edit_owner_vehicle',
        // 'destroy' => 'permissions:delete_owner_vehicle',
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


    // 🔹 Admin Central Payment Report
    Route::get('driver-payment-reports',[DriverController::class, 'driverPaymentReports']);
    // 🔹 Admin Central Renew / Licence Report
    Route::get('driver-renew-reports',[DriverController::class, 'driverRenewReports']);
    


    
    Route::get('drivers/me', [DriverController::class, 'getdriver']);
    Route::get('drivers/me/licence-history', [DriverController::class, 'licenceHistory']);
    Route::apiResource('drivers', DriverController::class);
    Route::get('payments/my-history', [PaymentController::class, 'myPaymentHistory']);


    Route::apiResource('vehicle-licenses', VehicleLicenseController::class)->middleware([
        'index' => 'permissions:view_vehicle_license',
        'show' => 'permissions:view_vehicle_license',
        'store' => 'permissions:create_vehicle_license',
        'update' => 'permissions:edit_vehicle_license',
        'destroy' => 'permissions:delete_vehicle_license',
    ]);

    Route::get('make-vehicle-license-payment', [VehicleLicenseController::class, 'makePayment']);

    Route::get('vehicle-payments', [VehiclePaymentController::class, 'index']);
    Route::post('vehicle-payments', [VehiclePaymentController::class, 'store']);


    Route::post('system-settings', [AppSettingController::class, 'update']);


    Route::post('drivers/{driver}/licence/payment', [DriverController::class, 'initiateLicencePayment'])->name('driver.licence.payment');
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

Route::get('system-settings', [AppSettingController::class, 'show']);


// Payment routes
Route::post('payment/ssl/success', [PaymentController::class, 'sslSuccess'])->name('ssl.payment.success');
Route::post('payment/ssl/fail', [PaymentController::class, 'sslFail'])->name('ssl.payment.fail');
Route::post('payment/ssl/cancel', [PaymentController::class, 'sslCancel'])->name('ssl.payment.cancel');
Route::post('vehicle-payments/{paymentId}/success', [VehiclePaymentController::class, 'success']);
