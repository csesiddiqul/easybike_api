<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Http\Requests\StoreOwnerRequest;
use App\Http\Requests\UpdateOwnerRequest;
use App\Http\Resources\OwnerResource;
use App\Models\User;
use App\Traits\HandleResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\SmsService;
use Illuminate\Support\Str;

class OwnerController extends Controller
{
    protected $smsService;
    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    use HandleResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Define the default per_page value
            $perPage = $request->get('per_page', 10);

            // Retrieve the search text if available
            $searchText = $request->get('searchText');

            $data = Owner::query()
                ->when($searchText, function ($query, $searchText) {
                    $query->where(function ($q) use ($searchText) {
                        // Owner টেবিলের নিজস্ব কলামে সার্চ
                        $q->where('father_or_husband_name', 'like', "%{$searchText}%")
                            ->orWhere('ward_number', 'like', "%{$searchText}%")
                            ->orWhere('mohalla_name', 'like', "%{$searchText}%")
                            ->orWhere('nid_number', 'like', "%{$searchText}%")
                            ->orWhere('present_address', 'like', "%{$searchText}%")
                            ->orWhere('permanent_address', 'like', "%{$searchText}%");

                        // User রিলেশনের ভেতর (Name, Phone, Email) সার্চ
                        $q->orWhereHas('user', function ($userQuery) use ($searchText) {
                            $userQuery->where('name', 'like', "%{$searchText}%")
                                ->orWhere('phone', 'like', "%{$searchText}%")
                                ->orWhere('email', 'like', "%{$searchText}%");
                        });
                    });
                })
                ->orderBy('id', 'desc')
                ->paginate($perPage);

            return $this->sendResponse("Data retrieved successfully", OwnerResource::collection($data)->response()->getData(true));
        } catch (\Throwable $th) {
            return $this->sendError("An error occurred while retrieving data", $th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOwnerRequest $request)
    {
        DB::beginTransaction();
        try {
            $adminRoleId = DB::table('roles')->where('name', 'Owner')->value('id');
            $password = $request->filled('password')
                ? $request->password
                : Str::random(8);

            $user = User::create([
                'name'     => $request->name,
                'phone'    => $request->phone,
                'email'    => $request->email,
                'role_id' => $adminRoleId,
                'password' => Hash::make($password),
            ]);
            $owner = Owner::create([
                'user_id'                     => $user->id,
                'father_or_husband_name'      => $request->father_or_husband_name,
                'ward_number'                 => $request->ward_number,
                'mohalla_name'                => $request->mohalla_name,
                'nid_number'                  => $request->nid_number,
                'birth_registration_number'   => $request->birth_registration_number,
                'present_address'             => $request->present_address,
                'permanent_address'           => $request->permanent_address,
                'image' => storeSingleFile($request, 'image', 'images/owners'),
            ]);

            $message = "Congratulations! Your Owner account is created.\n"
                . "Email: {$user->email}\n"
                . "Password: {$password}";

            $this->smsService->sendSms($user->phone, $message);

            DB::commit();
            return $this->sendResponse(
                'Owner created successfully',
                new OwnerResource(
                    $owner->load('user')

                )
            );
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError("An error occurred while creating Owner", $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Owner $owner)
    {
        try {
            return $this->sendResponse('Owner retrieved successfully', new OwnerResource($owner));
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError("An error occurred while retrieving Owner", $th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOwnerRequest $request, Owner $owner)
    {
        DB::beginTransaction();

        try {
            // 1️⃣ Update user table
            $owner->user->update([
                'name'  => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'status' => $request->status,
            ]);

            // 2️⃣ Update owner table
            $owner->update([
                'father_or_husband_name'    => $request->father_or_husband_name,
                'ward_number'               => $request->ward_number,
                'mohalla_name'              => $request->mohalla_name,
                'nid_number'                => $request->nid_number,
                'birth_registration_number' => $request->birth_registration_number,
                'present_address'           => $request->present_address,
                'permanent_address'         => $request->permanent_address,
                'image' => updateSingleFile(
                    $request,
                    'image',
                    'images/owners',
                    $owner->image
                ),
            ]);

            DB::commit();

            return $this->sendResponse(
                'Owner updated successfully',
                new OwnerResource($owner->fresh())
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->sendError('Update failed', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Owner $owner)
    {

        try {
            // delete owner image
            unlinkSingleFile($owner->image);

            // delete related user
            if ($owner->user) {
                $owner->user->delete();
            }

            // delete owner record
            $owner->delete();

            return response()->json([
                'success' => true,
                'message' => 'Owner and related User deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting Owner/User',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
