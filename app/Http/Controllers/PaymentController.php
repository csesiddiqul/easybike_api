<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\DriverLicenceRegistration;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function sslSuccess(Request $request)
    {
        DB::transaction(function () use ($request) {

            $payment = Payment::where('trx_id', $request->tran_id)
                ->where('status', 'pending')
                ->firstOrFail();

            $payment->update([
                'status' => 'paid',
                'payment_method' => 'sslcommerz',
                'paid_at' => now(),
            ]);

            if ($payment->type === 'driver_licence') {
                DriverLicenceRegistration::where('id', $payment->reference_id)
                    ->update([
                        'payment_status' => 'paid',
                        'approved_at' => now(),
                    ]);
            }
        });

        return redirect()->away(
            config('app.frontend_url') . '/payment-success'
        );
    }

    public function sslFail()
    {
        return redirect('/payment-failed');
    }

    public function sslCancel()
    {
        return redirect('/payment-cancelled');
    }

}
