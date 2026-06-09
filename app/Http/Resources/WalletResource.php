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
            'id' => $this->id,
            'address' => $this->address,
            'balance' => $this->relationLoaded('currency')
                ? number_format((float) $this->balance, $this->currency->decimal, '.', '')
                : $this->balance,
            'balance_usd' => round((float) ($this->balance_usd ?? 0), 2),
            'rate_usd' => (float) ($this->rate_usd ?? 0),
            'pnl_24h_amount' => round((float) ($this->pnl_24h_amount ?? 0), 2),
            'pnl_24h_percentage' => round((float) ($this->pnl_24h_percentage ?? 0), 2),
            'status' => $this->status,
            'currency_id' => $this->currency_id,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'transactions' => TransactionResource::collection($this->whenLoaded('transactions')),
        ];
    }
}
