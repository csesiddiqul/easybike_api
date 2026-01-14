<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBulkVehiclePaymentRequest;
use App\Http\Resources\VehiclePaymentResource;
use App\Models\VehiclePayment;
use App\Models\VehiclePaymentItem;
use App\Models\VehicleLicense;
use App\Services\SslCommerzService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehiclePaymentController extends BaseController
{
    public function index2()
    {
        $payments = VehiclePayment::with('items.licence')->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'data'    => $payments,
        ]);
    }



    public function index(Request $request)
    {
        $perPage    = $request->get('per_page', 10);
        $search     = $request->get('search');
        $ownerId    = $request->get('owner_id');
        $status     = $request->get('status');


        $query = VehiclePayment::query()
            ->with([
                'owner',
                'items.licence'
            ]);

        // 🔍 Search (transaction_no, owner name, phone)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_no', 'like', "%{$search}%");
            });
        }

        // 🎯 Filter by owner
        if ($ownerId) {
            $query->where('owner_id', $ownerId);
        }

        // 🎯 Filter by status
        if ($status !== null) {
            $query->where('status', $status);
        }

        $payments = $query
            ->latest()
            ->paginate($perPage);

        return VehiclePaymentResource::collection($payments)
            ->additional([
                'success' => true,
                'message' => 'Vehicle payments fetched successfully'
            ]);
    }


    public function store(StoreBulkVehiclePaymentRequest $request, SslCommerzService $ssl)
    {
        DB::beginTransaction();

        try {
            $trxId = 'Auto-' . strtoupper(Str::random(10));

            $payment = VehiclePayment::create([
                'owner_id'       => $request->owner_id,
                'total_amount'   => 0,
                'transaction_id'  => $trxId,
                'created_by'  => auth()->id(),
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
            ]);

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $licence = VehicleLicense::findOrFail($item['licence_id']);

                VehiclePaymentItem::create([
                    'vehicle_payment_id' => $payment->id,
                    'licence_id'         => $licence->id,
                    'amount'             => $licence->licence_fee,
                ]);

                $totalAmount += $licence->licence_fee;
            }

            $payment->update([
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            $baseUrl = url('/');

            if ($request->payment_method === 'cash') {

                // trx id optional (for record)
                $payment->update([
                    'payment_status' => 'success',
                    'paid_at'        => now(),
                ]);

                // activate licences
                foreach ($payment->items as $item) {
                    $licence = $item->licence;

                    $licence->update([
                        'payment_status' => 'paid',
                        'status'         => 'active',
                        'activated_at'   => now(),
                        'expired_at'     => $licence->fiscalYear->end_date,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Cash payment successful & licences activated.',
                    'data'    => $payment->load('items.licence'),
                ]);
            }

            // vehicle-payments/{paymentId}/success
            $sslResponse = $ssl->initiate([
                'tran_id' => $trxId,
                'total_amount' => $totalAmount,
                'product_name' => 'vehicle Payment',
                'cus_name'  => auth()->user()->name,
                'cus_phone' => auth()->user()->phone ?? '01700000000',
                'cus_email' => auth()->user()->email ?? 'no@mail.com',
                'success_url' => $baseUrl . '/api/vehicle-payments/' . $payment->id . '/success',
                'fail_url'    => config('sslcommerz.fail_url'),
                'cancel_url'  => config('sslcommerz.cancel_url'),

            ]);

            return response()->json([
                'payment_url' => $sslResponse['GatewayPageURL'],
            ]);

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Vehicle payment created successfully.',
            //     'data'    => $payment
            // ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function success($id)
    {
        $payment = VehiclePayment::with('items.licence.fiscalYear')
            ->findOrFail($id);

        DB::transaction(function () use ($payment) {

            $payment->update([
                'payment_status' => 'success',
                'paid_at'        => now(),
            ]);

            foreach ($payment->items as $item) {
                $licence = $item->licence;

                $licence->update([
                    'payment_status' => 'paid',
                    'status'         => 'active',
                    'activated_at'   => now(),
                    'expired_at'     => $licence->fiscalYear->end_date,
                ]);
            }
        });

        return redirect()->away(
            config('app.frontend_url') . '/vehicle-licenses-payment-success'
        );

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Payment successful & licences activated.',
        //     'data'    => $payment
        // ]);
    }
}
