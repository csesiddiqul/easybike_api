<?php

namespace App\Http\Requests;

use App\Enums\StatusEnum;
use App\Models\Medicine;
use App\Validation\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockRequest extends FormRequest
{
    use FailedValidation;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Stock
            'medicine_id' => 'required|exists:medicines,id',
            'quantity' => 'required|integer|min:1',
            'expiry_date' => 'required|date|date_format:Y-m-d|after:today',
        ];
    }

    // with validator rules for old stock quantity <= request stock quantity
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $stock = $this->stock;
            $requestQuantity = $this->quantity;

            $medicine = Medicine::find($this->medicine_id);

            if ($stock->quantity <= $requestQuantity || $medicine->quantity <= $requestQuantity) {
                $validator->errors()->add('quantity', 'Requested quantity must be less than or equal to current stock quantity.');
            }
        });
    }


}
