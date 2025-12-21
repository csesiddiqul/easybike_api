<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\User;
use App\Traits\HandleResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use HandleResponse;

    public function dashboard()
    {
        try {
            $patientCount = Patient::count();
            $adminUserCount = User::count();

            // Get all medicines
            $medicines = Medicine::all();

            $totalMedicineCount = $medicines->count();
            
            $totalStockQuantity = 0;
            $totalDistributedQuantity = 0;
            $totalRemainingQuantity = 0;
            $expired_quantity = 0;
            $damaged_quantity = 0;

            foreach ($medicines as $medicine) {
                $totalStockQuantity += $medicine->total_quantity;
                $totalDistributedQuantity += $medicine->distribution_quantity;
                $totalRemainingQuantity += $medicine->quantity;
                $expired_quantity += $medicine->expired_quantity;
                $damaged_quantity += $medicine->damaged_quantity;
            }

            // Calculate percentages
            $distributionPercentage = $totalStockQuantity > 0 ? ($totalDistributedQuantity / $totalStockQuantity) * 100 : 0;
            $remainingPercentage = $totalStockQuantity > 0 ? ($totalRemainingQuantity / $totalStockQuantity) * 100 : 0;
            $expired_quantity = $totalStockQuantity > 0 ? ($expired_quantity / $totalStockQuantity) * 100 : 0;
            $damaged_quantity = $totalStockQuantity > 0 ? ($damaged_quantity / $totalStockQuantity) * 100 : 0;
            $monthlyReport = $this->getMonthlyReport();
            // Prepare response data
            $data = [
                'total_patients' => $patientCount,
                'total_admin_users' => $adminUserCount,
                'total_medicines' => $totalMedicineCount,
                'total_stock_quantity' => $totalStockQuantity,
                'total_distributed_quantity' => $totalDistributedQuantity,
                'total_remaining_quantity' => $totalRemainingQuantity,
                'distribution_percentage' => $distributionPercentage,
                'remaining_percentage' => $remainingPercentage,
                'expired_quantity' => $expired_quantity,
                'damaged_quantity' => $damaged_quantity,

                'monthly_report' => $monthlyReport
            ];

            // Return the data with a success message
            return $this->sendResponse("Data retrieved successfully", $data);
        
        } catch (\Throwable $th) {
            // Handle any errors that occur during data retrieval
            return $this->sendError("An error occurred while retrieving data", $th->getMessage());
        }
    }


    private function getMonthlyReport()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($currentYear, $month, 1)->startOfMonth();
            $endDate = Carbon::create($currentYear, $month, 1)->endOfMonth();

            $stockQuantity = Medicine::whereBetween('created_at', [$startDate, $endDate])->sum('total_quantity');
            $distributionQuantity = Medicine::whereBetween('created_at', [$startDate, $endDate])->sum('distribution_quantity');
            $remainingQuantity = Medicine::whereBetween('created_at', [$startDate, $endDate])->sum('quantity');
            $damaged_quantity = Medicine::whereBetween('created_at', [$startDate, $endDate])->sum('damaged_quantity');
            $expired_quantity = Medicine::whereBetween('created_at', [$startDate, $endDate])->sum('expired_quantity');

            $monthlyData[] = [
                'month' => $startDate->format('F'),
                'stockQuantity' => $stockQuantity,
                'distributionQuantity' => $distributionQuantity,
                'remainingQuantity' => $remainingQuantity,
                'damaged_quantity' => $damaged_quantity,
                'expired_quantity' => $expired_quantity,
            ];
        }

        return $monthlyData;
    }
}
