<?php

namespace App\Http\Controllers;

use App\Http\Resources\DivisionResource;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\UpazilaResource;
use App\Http\Resources\UnionResource;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Traits\HandleResponse;
use Illuminate\Http\Request;

class FindAddressController extends Controller
{
    use HandleResponse;

    // Get divisions
    public function getDivisions() {
        try {
            $divisions = Division::all();
            return $this->sendResponse("Data retrieved successfully", DivisionResource::collection($divisions));
        } catch (\Throwable $th) {
            return $this->sendError("An error occurred while retrieving data", $th->getMessage());
        }
    }

    // Get districts by division id
    public function getDistricts($division_id) {
        try {
            $districts = Division::find($division_id)->districts;
            return $this->sendResponse("Data retrieved successfully", DistrictResource::collection($districts));
        } catch (\Throwable $th) {
            return $this->sendError("An error occurred while retrieving data", $th->getMessage());
        }
    }

    // Get upazilas by district id
    public function getUpazilas($district_id) {
        try {
            $upazilas = District::find($district_id)->upazilas;
            return $this->sendResponse("Data retrieved successfully", UpazilaResource::collection($upazilas));
        } catch (\Throwable $th) {
            return $this->sendError("An error occurred while retrieving data", $th->getMessage());
        }
    }

    // Get unions by upazila id
    public function getUnions($upazila_id) {
        try {
            $unions = Upazila::find($upazila_id)->unions;
            return $this->sendResponse("Data retrieved successfully", UnionResource::collection($unions));
        } catch (\Throwable $th) {
            return $this->sendError("An error occurred while retrieving data", $th->getMessage());
        }
    }
}
