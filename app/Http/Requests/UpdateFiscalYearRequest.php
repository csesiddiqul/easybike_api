<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Validation\FailedValidation;

class UpdateFiscalYearRequest extends FormRequest
{
    use FailedValidation;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admin হলেও fiscal year update করা যাবে না
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Custom message for forbidden update
     */
    protected function failedAuthorization()
    {
        abort(403, 'Fiscal year cannot be updated. Only activation is allowed.');
    }
}
