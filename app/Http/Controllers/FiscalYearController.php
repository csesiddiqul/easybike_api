<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Http\Requests\StoreFiscalYearRequest;
use App\Http\Requests\CorrectFiscalYearRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FiscalYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fiscalYears = FiscalYear::orderBy('start_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data'    => $fiscalYears,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFiscalYearRequest $request)
    {
        $hasActive = FiscalYear::where('is_active', true)->exists();

        $fiscalYear = FiscalYear::create([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'is_active'  => $hasActive ? false : true, // system controlled
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fiscal year created successfully',
            'data'    => $fiscalYear,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FiscalYear $fiscalYear)
    {
        return response()->json([
            'success' => true,
            'data'    => $fiscalYear,
        ]);
    }

    /**
     * Activate a fiscal year (custom action).
     */
    public function activate(FiscalYear $fiscalYear)
    {
        if ($fiscalYear->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This fiscal year is already active.',
            ], 422);
        }

        DB::transaction(function () use ($fiscalYear) {
            FiscalYear::where('is_active', true)->update([
                'is_active' => false,
            ]);

            $fiscalYear->update([
                'is_active' => true,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Fiscal year activated successfully.',
            'data'    => $fiscalYear->fresh(),
        ]);
    }

    /**
     * Correct the created wrong date and year the specified resource.
     */
    public function correct(CorrectFiscalYearRequest $request, FiscalYear $fiscalYear)
    {
        // Safety: usage check
        if ($fiscalYear->isUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'Fiscal year already in use. Correction not allowed.',
            ], 422);
        }

        $fiscalYear->update([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fiscal year corrected successfully.',
            'data'    => $fiscalYear->fresh(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FiscalYear $fiscalYear)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFiscalYearRequest $request, FiscalYear $fiscalYear)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FiscalYear $fiscalYear)
    {
        //
    }
}
