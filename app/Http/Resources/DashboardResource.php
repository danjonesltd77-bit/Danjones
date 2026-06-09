<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'user' => new UserResource($this['user']),
            'wallets' => WalletResource::collection($this['wallets']),
            'total_balance_usd' => round($this['total_balance_usd'], 2),
            'total_balance_ngn' => round($this['total_balance_ngn'], 2),
            'pnl_24h_amount' => round($this['pnl_24h_amount'] ?? 0.0, 2),
            'pnl_24h_percentage' => round($this['pnl_24h_percentage'] ?? 0.0, 2),
        ];
    }
}
