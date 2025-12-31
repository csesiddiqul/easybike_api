<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use App\Models\FiscalYear;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\DriverLicenceRegistration;
use App\Models\Payment;
use App\Services\SslCommerzService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\DriverResource;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;

class DriverController extends Controller
{

    public function driverSelectOptions()
    {
        try {
            $drivers = Driver::with('user')
                ->where('status', 'active')
                ->orderBy('id', 'desc')
                ->get();

            $options = $drivers->map(function ($driver) {
                return [
                    'id' => $driver->user->id,
                    'name' => $driver->user->name . ' (' . $driver->user->phone . ')',
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Driver select options retrieved successfully',
                'data'    => $options,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving Driver select options',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $drivers = Driver::with([
                'user',
                'latestLicence.fiscalYear'
            ])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => DriverResource::collection($drivers),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDriverRequest $request)
    {
        DB::beginTransaction();

        try {
            /* =====================
               Create User
            ===================== */
            $user = User::create([
                'name'     => $request->name,
                'phone'    => $request->phone,
                'email'    => $request->email,
                'user_name'=>$request->phone,
                'password' => Hash::make('123456'),
                'role_id'  => 3,
            ]);

            /* =====================
               Upload Image
            ===================== */
            $imagePath = null;
            if ($request->hasFile('driver_image')) {
                $imagePath = $request->file('driver_image')
                                    ->store('drivers', 'public');
            }

            /* =====================
               Create Driver
            ===================== */
            $driver = Driver::create([
                'user_id'                 => $user->id,
                'registration_number'     => $this->generateRegistrationNumber(),
                'driver_image'            => $imagePath,
                'nid'                     => $request->nid,
                'registration_date'       => now()->toDateString(),
                'years_of_experience'     => $request->years_of_experience,
                'present_address'         => $request->present_address,
                'permanent_address'       => $request->permanent_address,
                'status'                  => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Driver created successfully',
                'data'    => new DriverResource($driver->load('user')),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Driver creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Driver $driver)
    {
        return response()->json([
            'success' => true,
            'data'    => new DriverResource($driver->load('user')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDriverRequest $request, Driver $driver)
    {
        DB::beginTransaction();

        try {
            /* =====================
               Update User
            ===================== */
            $driver->user->update([
                'name'  => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
            ]);

            /* =====================
               Update Image
            ===================== */
            if ($request->hasFile('driver_image')) {
                $imagePath = $request->file('driver_image')
                                    ->store('drivers', 'public');
                $driver->driver_image = $imagePath;
            }

            /* =====================
               Update Driver Profile
            ===================== */
            $driver->update([
                'nid'                 => $request->nid,
                'years_of_experience' => $request->years_of_experience,
                'present_address'     => $request->present_address,
                'permanent_address'   => $request->permanent_address,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Driver updated successfully',
                'data'    => new DriverResource($driver->load('user')),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Driver update failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Driver $driver)
    {
        $driver->update(['status' => 'inactive']);
        $driver->user->update(['status' => 'inactive']);
        return response()->json([
            'success' => true,
            'message' => 'Driver deactivated successfully',
            'data'    => new DriverResource($driver->load('user')),
        ]);
    }

    /**
     * Generate unique driver registration number
     */
    private function generateRegistrationNumber(): string
    {
        $year = Carbon::now()->year;
        $lastDriver = Driver::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;

        if ($lastDriver && $lastDriver->registration_number) {
            // Extract last 6 digit number
            $parts = explode('-', $lastDriver->registration_number);
            $nextNumber = intval(end($parts)) + 1;
        }

        return 'DRV-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }













    // ================ Payments And Licence ======================//

    public function initiateLicencePayment(Driver $driver, SslCommerzService $ssl)
    {
        $fiscalYear = FiscalYear::where('is_active', true)->firstOrFail();

        $licence = DriverLicenceRegistration::firstOrCreate(
            [
                'driver_id' => $driver->id,
                'fiscal_year_id' => $fiscalYear->id,
            ],
            [
                'start_date' => $fiscalYear->start_date,
                'end_date'   => $fiscalYear->end_date,
                'payment_status' => 'unpaid',
            ]
        );

        if ($licence->payment_status === 'paid') {
            return response()->json([
                'message' => 'Licence already active'
            ], 422);
        }

        $trxId = 'DL-' . strtoupper(Str::random(10));

        $payment = Payment::updateOrCreate(
            [
                'type' => 'driver_licence',
                'reference_id' => $licence->id,
            ],
            [
                'user_id' => auth()->id(),
                'fiscal_year_id' => $fiscalYear->id,
                'amount' => config('fees.driver_licence'),
                'trx_id' => $trxId,
                'status' => 'pending',
            ]
        );

        $sslResponse = $ssl->initiate([
            'tran_id' => $trxId,
            'total_amount' => $payment->amount,

            'cus_name'  => auth()->user()->name,
            'cus_phone' => auth()->user()->phone ?? '01700000000',
            'cus_email' => auth()->user()->email ?? 'no@mail.com',
        ]);

        return response()->json([
            'payment_url' => $sslResponse['GatewayPageURL'],
        ]);
    }


    public function paymentSuccess(Request $request)
    {
        DB::transaction(function () use ($request) {

            $payment = Payment::where('trx_id', $request->tran_id)
                ->where('status', 'pending')
                ->firstOrFail();

            $payment->update([
                'status'         => 'paid',
                'payment_method' => 'sslcommerz',
                'paid_at'        => now(),
            ]);

            if ($payment->type === 'driver_licence') {
                DriverLicenceRegistration::where('id', $payment->reference_id)
                    ->update([
                        'payment_status' => 'paid',
                        'approved_at'    => now(),
                    ]);
            }
        });

        return response()->json(['message' => 'Licence activated']);
    }

}
