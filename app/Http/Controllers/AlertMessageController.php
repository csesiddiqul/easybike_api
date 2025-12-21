<?php

namespace App\Http\Controllers;

use App\Models\AlertMessage;
use App\Traits\HandleResponse;
use Illuminate\Http\Request;

class AlertMessageController extends Controller
{
    use HandleResponse;


    public function index(Request $request)
    {
        try {  
            $searchText = $request->get('searchText');
            // Define the default per_page value
            $perPage = $request->get('per_page', 10);
            
            $data =  AlertMessage::query()
                ->when($searchText, function ($query, $searchText) {
                    return $query->whereAny(['patient_name', 'prescription_code'], 'like', "%{$searchText}%");
                })
                ->orderBy('id', 'desc')->paginate($perPage ?? 10);
            
            return $this->sendResponse("Data retrieved successfully", $data);
        } catch (\Throwable $th) {
            return $this->sendError("An error occurred while retrieving data", $th->getMessage());
        }
    }
    
}
