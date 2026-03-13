<?php

namespace App\Http\Requests\P2P;

use Illuminate\Foundation\Http\FormRequest;

class InitiateTradeRequest extends FormRequest
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
            'advertisement_id' => ['required', 'exists:p2p_advertisements,id'],
            'amount' => ['required', 'numeric', 'min:0'], // Fiat amount the buyer wishes to trade
        ];
    }
}
