<?php

namespace App\Console\Commands;

use App\Enums\StockStatusEnum;
use App\Models\AlertMessage;
use App\Models\Medicine;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckMedicineExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-medicine-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for medicines that are about to expire or have expired and log them.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get current date
        $today = Carbon::now()->format('Y-m-d');
        
        // Flag to check if any issue was found
        $hasIssues = false;

        // Loop through all medicines
        $medicines = Medicine::active()->get();

        foreach ($medicines as $medicine) {
            // Ensure expiration reminder is set, default to 7 days if not provided
            $expirationReminderDays = $medicine->expiration_reminder_day ?? 7;

            // Calculate the date within the expiration reminder period
            $inDays = Carbon::now()->addDays($expirationReminderDays)->format('Y-m-d');

            // Fetch stocks of the medicine that are about to expire or have expired
            $expiredMedicinesAlert = Stock::active()
                ->where('medicine_id', $medicine->id)
                ->whereBetween('expiry_date', ["2024-08-01", $today])
                ->where('quantity', '>', 0)
                ->with("medicine")
                ->get();

            // Log expired or soon-to-expire medicines
            if ($expiredMedicinesAlert->isNotEmpty()) {
                $hasIssues = true;
                foreach ($expiredMedicinesAlert as $stock) {
                    // Check if the expiry date is in the past
                    if (Carbon::parse($stock->expiry_date)->isPast()) {
                        Log::warning("Medicine '{$medicine->name}' has already expired on {$stock->expiry_date} with a quantity of {$stock->quantity}.");
                        if(json_encode(StockStatusEnum::Active->value) == json_encode($stock->status)){
                            $stock->medicine->quantity -= $stock->quantity;
                            $stock->medicine->expired_quantity += $stock->quantity;
                            $stock->medicine->save();
                            
                            // change stock status
                            Stock::whereId($stock->id)->update([
                                "status" => StockStatusEnum::Expired->value,
                            ]) ;
                           
                        }
                    } else {
                        $data = "Medicine '{$medicine->name}' is about to expire on {$stock->expiry_date} with a quantity of {$stock->quantity}.";
                        Log::warning($data);
                        AlertMessage::create([
                            'data' => $data,
                        ]);
                    }
                }
            }
        }

        // Log if no issues were found
        if (!$hasIssues) {
            Log::info("No medicines are close to expiration.");
        }
    }
}
