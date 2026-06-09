<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
        $userId = $this->user()?->id;

        return [
            'phone' => 'required_without:avatar|nullable|string|unique:users,phone,'.$userId,
            'avatar' => 'required_without:phone|nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
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
            'phone.required_without' => 'Either the phone number or profile picture (avatar) must be provided.',
            'avatar.required_without' => 'Either the phone number or profile picture (avatar) must be provided.',
        ];
    }
}
