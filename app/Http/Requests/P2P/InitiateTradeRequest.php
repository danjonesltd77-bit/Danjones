<?php

namespace App\Http\Requests\P2P;

use App\Domains\P2P\Models\P2PAdvertisement;
use App\Enum\AdvertisementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $ad = P2PAdvertisement::find($this->advertisement_id);
        $isBuyAd = $ad && ($ad->type === AdvertisementType::BUY || $ad->type->value === 'buy');

        return [
            'advertisement_id' => ['required', 'exists:p2p_advertisements,id'],
            'amount' => ['required', 'numeric', 'min:0'], // Fiat amount the buyer wishes to trade
            'bank_account_id' => [
                $isBuyAd ? 'required' : 'nullable',
                Rule::exists('bank_accounts', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()?->id);
                }),
            ],
        ];
    }
}
