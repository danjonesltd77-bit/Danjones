<?php

namespace App\Http\Requests\P2P;

use App\Enum\AdvertisementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdvertisementRequest extends FormRequest
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
            'currency_id' => ['required', 'exists:currencies,id'],
            'type' => ['required', Rule::enum(AdvertisementType::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'min_limit' => ['required', 'numeric', 'min:0'],
            'max_limit' => ['required', 'numeric', 'gte:min_limit'],
            'terms' => ['nullable', 'string', 'max:1000'],
            'bank_account_id' => [
                'required_if:type,sell',
                'nullable',
                Rule::exists('bank_accounts', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()?->id);
                }),
            ],
        ];
    }
}
