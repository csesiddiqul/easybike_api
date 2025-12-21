<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockRequest;
use App\Models\Stock;
use App\Models\DistributionMedicine;
use App\Http\Requests\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Http\Resources\StoreReportResource;
use App\Models\Medicine;
use App\Models\Warehouse;


use App\Traits\HandleResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    use HandleResponse;

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        try {
            
            $startDate = $request->query('start_date') ?? Carbon::now()->format('Y-m-d');
            $endDate = $request->query('end_date');
            $status = $request->query('status');
            
            // Define the default per_page value, with a fallback to 10
            $perPage = (int) $request->get('per_page', 10);
    
            // Retrieve the search text if available
            $searchText = $request->get('searchText');
            

            $data = Stock::query()
            ->active($status)
            ->with(['warehouse',"stockedByStock", "medicine"])
            ->when($searchText, function ($query) use ($searchText) {
                // Apply the whereAny macro for search functionality
                $query->whereAny(
                    ['quantity'],
                    'LIKE',
                    "%{$searchText}%"
                );
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);
            return $this->sendResponse("Data retrieved successfully", StockResource::collection($data)->response()->getData(true));
        } catch (\Throwable $th) {
            return $this->sendError("An error occurred while retrieving data", $th->getMessage());
        }
    }



public function stockreport(Request $request)
{
    try {
        $startInput = $request->start_date;
        $endInput   = $request->end_date;

        $start = $startInput ? Carbon::parse($startInput)->startOfDay() : null;
        $end   = $endInput ? Carbon::parse($endInput)->endOfDay() : null;

        $perPage = (int) $request->get('per_page', 10);
        $search  = $request->get('searchText') ?? $request->get('search');
        $status  = $request->get('status');

        /*
        |--------------------------------------------------------------------------
        | STOCK SUBQUERY (NO DISTRIBUTION JOIN HERE)
        |--------------------------------------------------------------------------
        */
        $stockSub = DB::table('stocks')
            ->select(
                'medicine_id',

                // all time stock
                DB::raw('SUM(total_quantity) as total_stock'),

                // real remaining stock (this matches Medicine Stocks page)
                DB::raw('SUM(quantity) as remaining_from_stocks'),

                // previous stock
                DB::raw($start
                    ? "SUM(CASE WHEN created_at < '{$start->toDateTimeString()}' THEN total_quantity ELSE 0 END)"
                    : "0"
                . " as previous_stock"),

                // stock between date
                DB::raw(($start && $end)
                    ? "SUM(CASE WHEN created_at >= '{$start->toDateTimeString()}' AND created_at <= '{$end->toDateTimeString()}' THEN total_quantity ELSE 0 END)"
                    : "0"
                . " as stock_between")
            )
            ->when($status !== null, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->groupBy('medicine_id');

        /*
        |--------------------------------------------------------------------------
        | DISTRIBUTION SUBQUERY (SEPARATE)
        |--------------------------------------------------------------------------
        */
        $distributionSub = DB::table('distribution_medicines')
            ->select(
                'medicine_id',

                DB::raw('SUM(quantity) as total_distribution'),

                DB::raw($start
                    ? "SUM(CASE WHEN created_at < '{$start->toDateTimeString()}' THEN quantity ELSE 0 END)"
                    : "0"
                . " as previous_distribution"),

                DB::raw(($start && $end)
                    ? "SUM(CASE WHEN created_at >= '{$start->toDateTimeString()}' AND created_at <= '{$end->toDateTimeString()}' THEN quantity ELSE 0 END)"
                    : "0"
                . " as distribution_between")
            )
            ->groupBy('medicine_id');

        /*
        |--------------------------------------------------------------------------
        | MAIN QUERY
        |--------------------------------------------------------------------------
        */
        $query = DB::table('medicines')
            ->leftJoinSub($stockSub, 's', 'medicines.id', '=', 's.medicine_id')
            ->leftJoinSub($distributionSub, 'd', 'medicines.id', '=', 'd.medicine_id')
            ->select(
                'medicines.id as medicine_id',
                'medicines.name as medicine_name',

                DB::raw('COALESCE(s.previous_stock,0) as previous_stock'),
                DB::raw('COALESCE(s.stock_between,0) as stock_between'),
                DB::raw('COALESCE(s.total_stock,0) as total_stock'),
                DB::raw('COALESCE(s.remaining_from_stocks,0) as remaining_from_stocks'),

                DB::raw('COALESCE(d.previous_distribution,0) as previous_distribution'),
                DB::raw('COALESCE(d.distribution_between,0) as distribution_between'),
                DB::raw('COALESCE(d.total_distribution,0) as total_distribution'),

                // calculated remaining
                DB::raw('COALESCE(s.total_stock,0) - COALESCE(d.total_distribution,0) as remaining_calculated')
            );

        if ($search) {
            $query->where('medicines.name', 'like', "%{$search}%")
                  ->orWhere('medicines.id', $search);
        }

        $query->orderByDesc('total_stock');

        $results = $query->paginate($perPage);

        /*
        |--------------------------------------------------------------------------
        | TYPE CAST (SAFE)
        |--------------------------------------------------------------------------
        */
        $results->getCollection()->transform(function ($row) {
            foreach ($row as $key => $value) {
                if (is_numeric($value)) {
                    $row->$key = (int) $value;
                }
            }
            return $row;
        });

        return $this->sendResponse(
            'Data retrieved successfully',
            StoreReportResource::collection($results)->response()->getData(true)
        );

    } catch (\Throwable $th) {
        return $this->sendError(
            'An error occurred while retrieving data',
            $th->getMessage()
        );
    }
}



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockRequest $request)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();
            $stocked_by = auth()->user()->id;
    
            // Create a new warehouse entry
            $warehouse = Warehouse::create([
                'lot_memo_no' => $validatedData['lot_memo_no'],
                'stocked_by' => $stocked_by,
                'received_date' => $validatedData['received_date'],
            ]);
    
            // Create a new stock entry and update the medicine quantity
            if(isset($validatedData['stocks']) && is_array($validatedData['stocks'])) {
                foreach ($validatedData['stocks'] as $stockData) {
                    // Create the stock entry
                    Stock::create([
                        'warehouse_id' => $warehouse->id,
                        'medicine_id' => $stockData['medicine_id'],
                        'stocked_by' => $stocked_by,
                        'total_quantity' => $stockData['quantity'],
                        'quantity' => $stockData['quantity'],
                        'expiry_date' => $stockData['expiry_date'],
                    ]);
    
                    // Update the total quantity of the medicine
                    $medicine = Medicine::findOrFail($stockData['medicine_id']);
                    $medicine->total_quantity += $stockData['quantity'];
                    $medicine->quantity += $stockData['quantity'];
                    $medicine->save();
                }
            }
    
            DB::commit();
            return $this->sendResponse('Stock created successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError("An error occurred while creating stock", $th->getMessage());
        }
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Stock $stock)
    {
        try {
            return $this->sendResponse('Stock retrieved successfully', new StockResource($stock));
        } catch (\Throwable $th) {
            return $this->sendError("An error occurred while retrieving stock", $th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStockRequest $request, Stock $stock)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();

            $accessMinutes = config('myVariables.access_minutes'); // Fetch from config
            if (nowMinutes($stock->created_at) > $accessMinutes) {
                return $this->sendError('Access denied for actions after ' . $accessMinutes . ' minutes');
            }

             // Update the total quantity of the medicine
             $medicine = Medicine::findOrFail($stock['medicine_id']);
             if($medicine->quantity <= $validatedData['quantity'] || $stock->quantity <= $validatedData['quantity']){
                 return $this->sendError('Cannot delete stock. Remaining stock quantity is less than medicine quantity', 422);
             }

            $stock->medicine_id = $validatedData['medicine_id'];
            $stock->quantity -= $validatedData['quantity'];
            $stock->expiry_date = $validatedData['expiry_date'];
            $stock->save();

            // Update the total quantity of the medicine
            $medicine->total_quantity -=$validatedData['quantity'];
            $medicine->quantity -= $validatedData['quantity'];
            $medicine->save();

            DB::commit();
            return $this->sendResponse('Stock updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError("An error occurred while updating stock", $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stock $stock)
    {
        try {
            $accessMinutes = config('myVariables.access_minutes'); // Fetch from config
            if (nowMinutes($stock->created_at) > $accessMinutes) {
                return $this->sendError('Access denied for actions after ' . $accessMinutes . ' minutes');
            }
            // Update the total quantity of the medicine
            $medicine = Medicine::findOrFail($stock['medicine_id']);
            if($stock->quantity >= $medicine->quantity){
                return $this->sendError('Cannot delete stock. Remaining stock quantity is less than medicine quantity', 422);
            }
             $medicine->total_quantity -=$stock['quantity'];
             $medicine->quantity -= $stock['quantity'];
             $medicine->save();

            $stock->delete();
            return $this->sendResponse('Stock deleted successfully');
        } catch (\Throwable $th) {
            return $this->sendError("An error occurred while deleting stock", $th->getMessage());
        }
    }
}
