<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return false;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'loan_amount' => 'required|numeric|min:1',
            'number_of_loans' => 'required|integer|min:1',
            'installments_per_loan' => 'nullable|integer|min:1',
            'installment_period_minutes' => 'nullable|integer|min:1'
        ];
    }
}
