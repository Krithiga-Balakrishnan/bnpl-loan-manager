<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            //
            // 'loan_amount' => 'required|numeric|min:1',
            'loan_amount'       => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/'],
            'number_of_loans' => 'required|integer|min:1',
            'installments_per_loan' => 'nullable|integer|min:1',
            'installment_period_minutes' => 'nullable|integer|min:1'
        ];
    }
     /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'loan_amount.regex' => 'Loan amount must be a valid number with up to two decimal places.',
        ];
    }
}
