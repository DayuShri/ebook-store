<?php

namespace App\Http\Requests;

class WalletCreditRequest extends BaseFormRequest
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
            'user_id' => ['required', 'string', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_id' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User not found',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Amount must be at least 0.01',
            'reference_id.max' => 'Reference ID cannot exceed 255 characters',
            'description.max' => 'Description cannot exceed 500 characters',
        ];
    }
}
