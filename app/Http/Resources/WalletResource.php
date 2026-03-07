<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Wallet\Models\Wallet */
class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'address'     => $this->address,
            'balance'     => $this->relationLoaded('currency')
                ? number_format((float) $this->balance, $this->currency->decimal, '.', '')
                : $this->balance,
            'status'      => $this->status,
            'currency_id' => $this->currency_id,
            'currency'    => new CurrencyResource($this->whenLoaded('currency')),
        ];
    }
}
