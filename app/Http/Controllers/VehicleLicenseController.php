<?php

namespace App\Http\Controllers;

use App\Models\VehicleLicense;
use App\Http\Requests\StoreVehicleLicenseRequest;
use App\Http\Requests\UpdateVehicleLicenseRequest;
use App\Models\FiscalYear;
use App\Models\Owner;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleLicenseController extends Controller
{

    public function makePayment(Request $request)
    {
        $query = VehicleLicense::query()
            ->with([
                'vehicle:id,registration_number,owner_id',
                'owner:id,user_id', // user_id relation এর জন্য
                'owner.user:id,name,phone,email',
                'fiscalYear:id,name,start_date,end_date',
            ]);


        if ($request->filled('user_id')) {
            $query->whereHas('owner', function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }


        // Owner ID search
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        // Owner phone search
        if ($request->filled('phone')) {
            $query->whereHas('owner.user', function ($q) use ($request) {
                $q->where('phone', 'like', "%{$request->phone}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Fetch all matching licences
        $licenses = $query->orderByDesc('fiscal_year_id')->get();

        // Group by owner → vehicles → licences
        $grouped = $licenses->groupBy('owner_id')->map(function ($ownerLicenses) {

            $owner = $ownerLicenses->first()->owner;

            $ownerUser = $owner->user;

            $vehicles = $ownerLicenses->groupBy('vehicle_id')->map(function ($vehicleLicenses) {

                $vehicle = $vehicleLicenses->first()->vehicle;

                $licences = $vehicleLicenses->map(function ($item) {
                    return [
                        'id'             => $item->id,
                        'fiscal_year'    => $item->fiscalYear->name ?? null,
                        'licence_fee'    => $item->licence_fee,
                        'status'         => $item->status,
                        'payment_status' => $item->payment_status,
                        'is_payable'     => in_array($item->status, ['pending', 'expired']) && $item->payment_status === 'unpaid',
                        'activated_at'   => $item->activated_at,
                        'expired_at'     => $item->expired_at,
                    ];
                })->values();

                return [
                    'vehicle_id'         => $vehicle->id,
                    'registration_number' => $vehicle->registration_number,
                    'licences'           => $licences,
                ];
            })->values(); // reset vehicle keys

            return [
                'owner_id'    => $owner->id,
                'owner_name'  => $ownerUser->name ?? null,
                'owner_phone' => $ownerUser->phone ?? null,
                'vehicles'    => $vehicles,
            ];
        })->values(); // reset owner keys

        return response()->json([
            'success' => true,
            'message' => 'Vehicle licences fetched successfully.',
            'data'    => $grouped
        ]);
    }




    public function success($paymentId)
    {
        $payment = \App\Models\Payment::findOrFail($paymentId);

        DB::transaction(function () use ($payment) {
            $payment->update([
                'payment_status' => 'success',
                'paid_at'        => now(),
            ]);

            foreach ($payment->items as $item) {
                $licence = \App\Models\VehicleLicense::where([
                    'vehicle_id' => $item->vehicle_id,
                    'fiscal_year_id' => $item->fiscal_year_id,
                ])->first();

                $licence->update([
                    'payment_status' => 'paid',
                    'status'         => 'active',
                    'activated_at'   => now(),
                    'expired_at'     => $licence->fiscalYear->end_date,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment completed and licences activated.',
            'data'    => $payment
        ]);
    }



    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = VehicleLicense::query()
            ->with([
                'vehicle:id,registration_number,owner_id',
                'owner:id,user_id', // user_id relation এর জন্য
                'owner.user:id,name,phone,email',
                'fiscalYear:id,name,start_date,end_date',
            ]);

        // Owner ID search
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        // Owner phone search
        if ($request->filled('phone')) {
            $query->whereHas('owner.user', function ($q) use ($request) {
                $q->where('phone', 'like', "%{$request->phone}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Fetch all matching licences
        $licenses = $query->orderByDesc('fiscal_year_id')->get();

        // Group by owner → vehicles → licences
        $grouped = $licenses->groupBy('owner_id')->map(function ($ownerLicenses) {

            $owner = $ownerLicenses->first()->owner;

            $ownerUser = $owner->user;

            $vehicles = $ownerLicenses->groupBy('vehicle_id')->map(function ($vehicleLicenses) {

                $vehicle = $vehicleLicenses->first()->vehicle;

                $licences = $vehicleLicenses->map(function ($item) {
                    return [
                        'fiscal_year'    => $item->fiscalYear->name ?? null,
                        'licence_fee'    => $item->licence_fee,
                        'status'         => $item->status,
                        'payment_status' => $item->payment_status,
                        'is_payable'     => in_array($item->status, ['pending', 'expired']) && $item->payment_status === 'unpaid',
                        'activated_at'   => $item->activated_at,
                        'expired_at'     => $item->expired_at,
                    ];
                })->values();

                return [
                    'vehicle_id'         => $vehicle->id,
                    'registration_number' => $vehicle->registration_number,
                    'licences'           => $licences,
                ];
            })->values(); // reset vehicle keys

            return [
                'owner_id'    => $owner->id,
                'owner_name'  => $ownerUser->name ?? null,
                'owner_phone' => $ownerUser->phone ?? null,
                'vehicles'    => $vehicles,
            ];
        })->values(); // reset owner keys

        return response()->json([
            'success' => true,
            'message' => 'Vehicle licences fetched successfully.',
            'data'    => $grouped
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $owners = Owner::with('vehicles')->get();

        foreach ($owners as $owner) {
            foreach ($owner->vehicles as $vehicle) {
                $licence = VehicleLicense::firstOrCreate(
                    [
                        'vehicle_id'     => $vehicle->id,
                        'fiscal_year_id' => FiscalYear::getActiveFiscalYear()?->id,
                    ],
                    [
                        'owner_id'       => $owner->id,
                        'licence_fee'    => $request->licence_fee ?? '00.00',
                        'status'         => 'pending',
                        'payment_status' => 'unpaid',
                    ]
                );

                if (!$licence->wasRecentlyCreated) {
                    continue;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Vehicle license generated successfully.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleLicense $vehicleLicense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleLicenseRequest $request, VehicleLicense $vehicleLicense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleLicense $vehicleLicense)
    {
        //
    }
}
