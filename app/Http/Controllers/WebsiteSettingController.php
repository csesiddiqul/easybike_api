<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreWebsiteSettingRequest;
use App\Http\Resources\WebsiteSettingResource;
use App\Models\WebsiteSetting;
use App\Traits\HandleResponse;
use Illuminate\Http\Request;

class WebsiteSettingController extends Controller
{
    use HandleResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve the first record or handle no record found
            $settings = WebsiteSetting::first(); // Assuming only one record exists
            if ($settings) {
                return $this->sendResponse('Website settings retrieved successfully', new WebsiteSettingResource($settings));
            } else {
                return $this->sendError('Website settings not found');
            }
        } catch (\Throwable $th) {
            return $this->sendError("An error occurred while retrieving website settings", $th->getMessage());
        }
    }

    /**
     * Store or update the resource in storage.
     */
    public function storeOrUpdate(StoreWebsiteSettingRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Check if any settings already exist
            $settings = WebsiteSetting::first();

            if ($settings) {
                // If settings exist, update the existing record
                $settings->update($validatedData);
            } else {
                // If no settings exist, create a new record
                $settings = WebsiteSetting::create($validatedData);
            }

            return $this->sendResponse('Website settings updated successfully', new WebsiteSettingResource($settings));
        } catch (\Throwable $th) {
            return $this->sendError("An error occurred while updating website settings", $th->getMessage());
        }
    }
}