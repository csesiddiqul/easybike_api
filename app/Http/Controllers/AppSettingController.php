<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Requests\UpdateAppSettingRequest;
use App\Http\Resources\AppSettingResource;

class AppSettingController extends Controller
{
    /**
     * SHOW SETTINGS
     */
    public function show()
    {
        $setting = AppSetting::first();

        return response()->json([
            'success' => true,
            'data' => new AppSettingResource($setting),
        ]);
    }

    /**
     * UPDATE SETTINGS
     */
    public function update(UpdateAppSettingRequest $request)
    {
        DB::beginTransaction();

        try {
            $setting = AppSetting::firstOrCreate([]);

            /* =====================
               SYSTEM LOGO
            ===================== */
            if ($request->hasFile('system_logo')) {
                $file = $request->file('system_logo');
                $fileName = 'system_logo_' . time() . '.' . $file->getClientOriginalExtension();

                $file->move(public_path('app-settings/system'), $fileName);

                $setting->system_logo = 'app-settings/system/' . $fileName;
            }

            /* =====================
               CITY LOGO
            ===================== */
            if ($request->hasFile('city_corporation_logo')) {
                $file = $request->file('city_corporation_logo');
                $fileName = 'city_logo_' . time() . '.' . $file->getClientOriginalExtension();

                $file->move(public_path('app-settings/city'), $fileName);

                $setting->city_corporation_logo = 'app-settings/city/' . $fileName;
            }

            /* =====================
               UPDATE SETTINGS DATA
            ===================== */
            $setting->update([
                'system_name'                 => $request->system_name,
                'city_corporation_name'       => $request->city_corporation_name,
                'city_corporation_phone'      => $request->city_corporation_phone,
                'vehicle_charge_per_year'     => $request->vehicle_charge_per_year,
                'driver_licence_renew_charge' => $request->driver_licence_renew_charge,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'System settings updated successfully',
                'data'    => new AppSettingResource($setting),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'System setting update failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
