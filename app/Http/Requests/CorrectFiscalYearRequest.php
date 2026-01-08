<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\FiscalYear;
use App\Validation\FailedValidation;

class CorrectFiscalYearRequest extends FormRequest
{
    use FailedValidation;
    
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

            if (
                date('m-d', strtotime($start)) !== '07-01' ||
                date('m-d', strtotime($end)) !== '06-30'
            ) {
                $validator->errors()->add(
                    'start_date',
                    'Fiscal year must start on 1 July.'
                );

                $validator->errors()->add(
                    'end_date',
                    'Fiscal year must end on 30 June.'
                );
            }

            if (date('Y', strtotime($end)) - date('Y', strtotime($start)) !== 1) {
                $validator->errors()->add(
                    'end_date',
                    'Fiscal year must be exactly one year.'
                );
            }

            if ($this->fiscalYear->is_active) {
                $validator->errors()->add(
                    'name',
                    'Active fiscal year cannot be updated.'
                );
            }
        });
    }
}
