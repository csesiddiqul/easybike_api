<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\FiscalYear;

class CorrectFiscalYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->role
            && auth()->user()->role->name === 'Super Admin';
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'unique:fiscal_years,name,' . $this->fiscalYear->id,
            ],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $start = $this->start_date;
            $end   = $this->end_date;

            // Must be 1 July – 30 June
            if (
                date('m-d', strtotime($start)) !== '07-01' ||
                date('m-d', strtotime($end)) !== '06-30'
            ) {
                $validator->errors()->add(
                    'start_date',
                    'Fiscal year must start on 1 July and end on 30 June.'
                );
            }

            // Must be exactly 1 year
            if (date('Y', strtotime($end)) - date('Y', strtotime($start)) !== 1) {
                $validator->errors()->add(
                    'end_date',
                    'Fiscal year must be exactly one year.'
                );
            }

            // Cannot correct active fiscal year
            if ($this->fiscalYear->is_active) {
                $validator->errors()->add(
                    'fiscal_year',
                    'Active fiscal year cannot be corrected.'
                );
            }
        });
    }
}
