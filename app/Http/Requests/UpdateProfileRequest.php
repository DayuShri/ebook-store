<?php

namespace App\Http\Requests;

class UpdateProfileRequest extends BaseFormRequest
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
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'profile_picture_url' => ['nullable', 'url'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'phone_number' => ['nullable', 'string', 'max:20'],
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
            'full_name.required' => 'Full name is required',
            'profile_picture_url.url' => 'Profile picture URL must be a valid URL',
            'date_of_birth.before' => 'Date of birth must be in the past',
            'phone_number.max' => 'Phone number cannot exceed 20 characters',
        ];
    }
}
