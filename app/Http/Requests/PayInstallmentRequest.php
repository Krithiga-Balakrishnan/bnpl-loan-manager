<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayInstallmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'installment' => ['required', 'integer', 'exists:installments,id']
        ];
    }

    public function validationData()
    {
        return array_merge($this->all(), [
            'installment' => $this->route('installment')
        ]);
    }
}
