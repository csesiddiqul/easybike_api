<?php

namespace App\Http\Requests;

use App\Models\Medicine;
use App\Validation\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockRequest extends FormRequest
{
    use FailedValidation;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Warehouse
            'lot_memo_no' => 'required|string|max:50|unique:warehouses',
            'received_date' => 'required|date|date_format:Y-m-d|before_or_equal:today',

            // Stock
            'stocks' => 'required|array',
            'stocks.*.medicine_id' => 'required|exists:medicines,id',
            'stocks.*.quantity' => 'required|integer|min:1',
            'stocks.*.expiry_date' => 'nullable|date|date_format:Y-m-d|after:today',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
   
}
