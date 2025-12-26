<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\FiscalYear;

class StoreFiscalYearRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->role
            && auth()->user()->role->name === 'Super Admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'unique:fiscal_years,name',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],
        ];
    }

    /**
     * Custom validation after basic rules
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $start = $this->start_date;
            $end   = $this->end_date;

            // Must be 1 July to 30 June
            if (
                date('m-d', strtotime($start)) !== '07-01' ||
                date('m-d', strtotime($end)) !== '06-30'
            ) {
                $validator->errors()->add(
                    'start_date',
                    'Fiscal year must start on 1 July and end on 30 June.'
                );
            }

            // Overlapping fiscal year check
            $overlap = FiscalYear::where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_date', '<=', $start)
                         ->where('end_date', '>=', $end);
                  });
            })->exists();

            if ($overlap) {
                $validator->errors()->add(
                    'start_date',
                    'Fiscal year date range overlaps with an existing fiscal year.'
                );
            }
        });
    }
}
