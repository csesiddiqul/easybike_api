<?php

namespace App\Console\Commands;

use App\Models\AlertMessage;
use App\Models\Medicine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckMedicineSoldOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-medicine-sold-out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for medicines that are sold out or almost sold out and log them.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now()->format('Y-m-d');
        
        // Loop through all medicines
        $medicines = Medicine::active()->get();

        // Flag to check if any issue was found
        $hasIssues = false;

        foreach ($medicines as $medicine) {
            // Ensure the alert_quantity is set, default to 1 if not provided
            $alertQuantity = $medicine->alert_quantity ?? 1;
            $remainingQuantity = $medicine->quantity ?? 0;

            // Log low stock medicines
            if ($remainingQuantity <= $alertQuantity && $remainingQuantity > 0) {
                $hasIssues = true;
                $data = "Medicine '{$medicine->name}' is almost sold out with a remaining quantity of {$remainingQuantity}."; 
                Log::warning($data);
                AlertMessage::create([
                    'data' => $data,
                ]);
                // Notification::route('mail', 'admin@example.com') // Replace with your recipient(s)
                //             ->notify(new MedicineStockAlert($medicine, 'almost sold out'));
            
            }

            // Log sold out medicines
            if ($remainingQuantity <= 0) {
                $hasIssues = true;
                $data = "Medicine '{$medicine->name}' is completely sold out.";
                Log::warning($data);
                AlertMessage::create([
                    'data' => $data,
                ]);
                // Notification::route('mail', 'admin@example.com') // Replace with your recipient(s)
                //             ->notify(new MedicineStockAlert($medicine, 'sold out'));
            }
        }

        if (!$hasIssues) {
            Log::info("No medicines are sold out or near sell-out.");
        }
    }
}
