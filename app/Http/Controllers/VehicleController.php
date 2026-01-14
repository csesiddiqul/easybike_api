<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\FiscalYear;
use App\Models\VehicleDriverAssignment;
use App\Models\VehicleLicense;
use App\Traits\HandleResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    use HandleResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage       = $request->get('per_page', 10);
            $searchText    = $request->get('searchText');
            $ownerUserId   = $request->get('owner_user_id');


            $data = Vehicle::with([
                'activeLicense',
                'owner',
                'owner.user',
                'currentDriver.driver:id,name,email,phone,user_name,role_id,status',
            ])
                // 🔍 searchText filter
                ->when($searchText, function ($query, $searchText) {
                    $query->where(function ($q) use ($searchText) {

                        // Vehicle table search
                        $q->where('vehicle_type', 'like', "%{$searchText}%")
                            ->orWhere('supplier_type', 'like', "%{$searchText}%")
                            ->orWhere('registration_number', 'like', "%{$searchText}%")
                            ->orWhere('vehicle_model_name', 'like', "%{$searchText}%")
                            ->orWhere('chassis_number', 'like', "%{$searchText}%")
                            ->orWhere('status', 'like', "%{$searchText}%");

                        // Owner User search
                        $q->orWhereHas('owner.user', function ($ownerQuery) use ($searchText) {
                            $ownerQuery->where('name', 'like', "%{$searchText}%")
                                ->orWhere('phone', 'like', "%{$searchText}%")
                                ->orWhere('email', 'like', "%{$searchText}%")
                                ->orWhere('user_name', 'like', "%{$searchText}%");
                        });

                        // Current Driver search
                        $q->orWhereHas('currentDriver.driver', function ($driverQuery) use ($searchText) {
                            $driverQuery->where('name', 'like', "%{$searchText}%")
                                ->orWhere('phone', 'like', "%{$searchText}%")
                                ->orWhere('email', 'like', "%{$searchText}%")
                                ->orWhere('user_name', 'like', "%{$searchText}%");
                        });
                    });
                })
                // 🔍 owner_role_id filter
                ->when($ownerUserId, function ($query, $ownerUserId) {
                    $query->whereHas('owner.user', function ($ownerQuery) use ($ownerUserId) {
                        $ownerQuery->where('id', $ownerUserId);
                    });
                })
                ->orderBy('id', 'desc')
                ->paginate($perPage);

            return $this->sendResponse(
                'Vehicle list retrieved successfully',
                VehicleResource::collection($data)->response()->getData(true)
            );
        } catch (\Throwable $th) {
            return $this->sendError(
                'An error occurred while retrieving vehicles',
                $th->getMessage()
            );
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehicleRequest $request)
    {
        try {
            $vehicle = DB::transaction(function () use ($request) {
                $vehicle = Vehicle::create([
                    'owner_id' => $request->owner_id,
                    'vehicle_type' => $request->vehicle_type,
                    'supplier_type' => $request->supplier_type,
                    'registration_number' => Vehicle::generateRegistrationNumber(),
                    'vehicle_model_name' => $request->vehicle_model_name,
                    'chassis_number' => $request->chassis_number,
                    'status' => $request->status,
                ]);

                VehicleDriverAssignment::create([
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $request->driver_id,
                    'start_date' => now(),
                    'status' => 'active',
                ]);

                VehicleLicense::create([
                    'owner_id'       => $request->owner_id,
                    'vehicle_id'     => $vehicle->id,
                    'fiscal_year_id' => FiscalYear::getActiveFiscalYear()?->id,
                    'licence_fee'    => $request->licence_fee ?? '400.00',
                    'status'         => 'pending',
                    'payment_status' => 'unpaid',
                ]);

                return $vehicle;
            });

            $vehicle->load([
                'owner',
                'currentDriver.driver:id,name,email,phone,user_name,role_id,status',
            ]);

            // ✅ Response
            return $this->sendResponse(
                'Vehicle created successfully',
                new VehicleResource($vehicle), // include active driver
            );
        } catch (\Throwable $th) {
            return $this->sendError(
                "An error occurred while creating Vehicle",
                $th->getMessage()
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $vehicle = Vehicle::with([
                'owner',
                'owner.user:id,name,email,phone,user_name,role_id,status',
                'currentDriver.driver:id,name,email,phone,user_name,role_id,status',
            ])->findOrFail($id);
            return $this->sendResponse('Vehicle retrieved successfully', new VehicleResource($vehicle));
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError("An error occurred while retrieving Vehicle", $th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        try {
            $updatedVehicle = DB::transaction(function () use ($request, $vehicle) {
                // 1️⃣ Update vehicle info
                $vehicle->update([
                    'owner_id' => $request->owner_id,
                    'vehicle_type' => $request->vehicle_type,
                    'supplier_type' => $request->supplier_type,
                    'vehicle_model_name' => $request->vehicle_model_name,
                    'chassis_number' => $request->chassis_number,
                    'status' => $request->status,
                ]);

                // 2️⃣ Handle driver assignment
                // 2a. Deactivate previous active assignment
                // Check if there is a current driver
                if ($vehicle->currentDriver) {
                    // Only release if the driver_id is different
                    if ($vehicle->currentDriver->driver_id != $request->driver_id) {
                        // Release old driver
                        $vehicle->currentDriver->update([
                            'status' => 'released',
                            'end_date' => now(),
                        ]);

                        // Create new assignment
                        VehicleDriverAssignment::create([
                            'vehicle_id' => $vehicle->id,
                            'driver_id' => $request->driver_id,
                            'start_date' => now(),
                            'status' => 'active',
                        ]);
                    }
                }

                // If no current driver exists, directly create new assignment
                if (!$vehicle->currentDriver) {
                    VehicleDriverAssignment::create([
                        'vehicle_id' => $vehicle->id,
                        'driver_id' => $request->driver_id,
                        'start_date' => now(),
                        'status' => 'active',
                    ]);
                }

                return $vehicle;
            });

            $updatedVehicle->load([
                'owner',
                'currentDriver.driver:id,name,email,phone,user_name,role_id,status',
            ]);

            return $this->sendResponse(
                'Vehicle updated successfully',
                new VehicleResource($updatedVehicle)
            );
        } catch (\Throwable $th) {
            return $this->sendError(
                "An error occurred while updating Vehicle",
                $th->getMessage()
            );
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        try {
            DB::transaction(function () use ($vehicle) {

                $vehicle->currentDriver?->update([
                    'status' => 'inactive',
                    'end_date' => now(),
                ]);

                $vehicle->delete();
            });

            return $this->sendResponse(
                'Vehicle deleted successfully',
                null
            );
        } catch (\Throwable $th) {
            return $this->sendError(
                "An error occurred while deleting Vehicle",
                $th->getMessage()
            );
        }
    }
}
