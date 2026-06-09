<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Wallet\Models\Currency */
class CurrencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'image' => $this->image,
            'decimal_places' => $this->decimal,
            'is_crypto' => $this->is_crypto,
            'is_active' => $this->is_active,
            'price_change_24h' => $this->price_change_24h,
        ];
    }
}
