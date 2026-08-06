<?php

namespace App\Http\Controllers\Api;

use App\Domains\P2P\Actions\CancelTradeAction;
use App\Domains\P2P\Actions\CloseAdvertisementAction;
use App\Domains\P2P\Actions\CompleteTradeAction;
use App\Domains\P2P\Actions\CreateAdvertisementAction;
use App\Domains\P2P\Actions\DisputeTradeAction;
use App\Domains\P2P\Actions\InitiateTradeAction;
use App\Domains\P2P\Actions\MarkTradePaidAction;
use App\Domains\P2P\Models\P2PAdvertisement;
use App\Domains\P2P\Models\P2PTrade;
use App\Enum\TradeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\P2P\DisputeTradeRequest;
use App\Http\Requests\P2P\InitiateTradeRequest;
use App\Http\Requests\P2P\MarkTradePaidRequest;
use App\Http\Requests\P2P\StoreAdvertisementRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class P2PController extends Controller
{
    public function profile(Request $request, ?User $user = null): JsonResponse
    {
        $targetUser = $user ?? $request->user();
        $userId = $targetUser->id;

        $stats = P2PTrade::where(function ($query) use ($userId) {
            $query->where('buyer_id', $userId)
                ->orWhere('seller_id', $userId);
        })
            ->selectRaw('
                COUNT(*) as total_trades,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as total_completed,
                AVG(CASE WHEN status = ? THEN UNIX_TIMESTAMP(updated_at) - UNIX_TIMESTAMP(created_at) ELSE NULL END) as avg_time
            ', [TradeStatus::COMPLETED->value, TradeStatus::COMPLETED->value])
            ->first();

        $totalTrades = (int) ($stats->total_trades ?? 0);
        $totalCompleted = (int) ($stats->total_completed ?? 0);
        $completionRate = $totalTrades > 0
            ? round(($totalCompleted / $totalTrades) * 100, 2)
            : 0;

        $avgTransactionTimeSeconds = $stats->avg_time !== null ? (float) $stats->avg_time : null;

        $avgTransactionTimeFormatted = null;
        if ($avgTransactionTimeSeconds !== null) {
            $avgTransactionTimeFormatted = $this->formatDuration($avgTransactionTimeSeconds);
        }

        return ApiResponse::success([
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'initials' => $targetUser->initials(),
                'kyc_status' => $targetUser->kyc_status,
                'joined_at' => $targetUser->created_at->toIso8601String(),
            ],
            'bank_accounts' => $targetUser->bankAccounts()->with('bank')->get(),
            'statistics' => [
                'total_trades' => $totalTrades,
                'total_completed' => $totalCompleted,
                'completion_rate' => $completionRate,
                'avg_transaction_time_seconds' => $avgTransactionTimeSeconds,
                'avg_transaction_time_formatted' => $avgTransactionTimeFormatted,
            ],
        ]);
    }

    private function formatDuration(float $seconds): string
    {
        $seconds = round($seconds);
        if ($seconds < 60) {
            return $seconds.'s';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            return $remainingSeconds > 0 ? "{$minutes}m {$remainingSeconds}s" : "{$minutes}m";
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $remainingMinutes > 0 ? "{$hours}h {$remainingMinutes}m" : "{$hours}h";
    }

    public function indexAds()
    {
        $ads = P2PAdvertisement::where('is_active', true)
            ->with(['user', 'currency', 'bankAccount.bank'])
            ->latest()
            ->get();

        return ApiResponse::success(['ads' => $ads]);
    }

    public function myAds(Request $request)
    {
        $ads = $request->user()->p2pAdvertisements()
            ->with(['currency', 'bankAccount.bank'])
            ->latest()
            ->get();

        return ApiResponse::success(['ads' => $ads]);
    }

    public function myTrades(Request $request)
    {
        $trades = P2PTrade::where(function ($query) use ($request) {
            $query->where('buyer_id', $request->user()->id)
                ->orWhere('seller_id', $request->user()->id);
        })
            ->with(['advertisement.bankAccount.bank', 'seller', 'buyer', 'currency', 'bankAccount.bank'])
            ->latest()
            ->get();

        return ApiResponse::success(['trades' => $trades]);
    }

    public function closeAd(Request $request, P2PAdvertisement $ad, CloseAdvertisementAction $action)
    {
        try {
            $ad = $action->execute($request->user(), $ad);

            return ApiResponse::success(['message' => 'Advertisement closed successfully', 'ad' => $ad]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function storeAd(StoreAdvertisementRequest $request, CreateAdvertisementAction $action)
    {
        try {
            $ad = $action->execute($request->user(), $request->validated());
            $ad->load(['user', 'currency', 'bankAccount.bank']);

            return ApiResponse::success(['message' => 'Advertisement created successfully', 'data' => $ad], 201);
        } catch (\Exception $e) {

            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function initiateTrade(InitiateTradeRequest $request, InitiateTradeAction $action)
    {
        try {
            $ad = P2PAdvertisement::findOrFail($request->advertisement_id);
            $trade = $action->execute($request->user(), $ad, $request->amount, $request->bank_account_id);
            $trade->load(['advertisement.bankAccount.bank', 'seller', 'buyer', 'currency', 'bankAccount.bank']);

            return ApiResponse::success(['message' => 'Trade initiated successfully', 'data' => $trade], 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function markTradePaid(MarkTradePaidRequest $request, P2PTrade $trade, MarkTradePaidAction $action)
    {
        try {
            $path = $request->file('payment_proof')->store('p2p_proofs', 'public');
            $paymentProofUrl = Storage::disk('public')->url($path);

            $trade = $action->execute($request->user(), $trade, $paymentProofUrl);
            $trade->load(['advertisement.bankAccount.bank', 'seller', 'buyer', 'currency', 'bankAccount.bank']);

            return ApiResponse::success(['message' => 'Trade marked as paid', 'data' => $trade]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function completeTrade(Request $request, P2PTrade $trade, CompleteTradeAction $action)
    {
        try {
            $trade = $action->execute($request->user(), $trade);
            $trade->load(['advertisement.bankAccount.bank', 'seller', 'buyer', 'currency', 'bankAccount.bank']);

            return ApiResponse::success(['message' => 'Trade completed and crypto released', 'data' => $trade]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function cancelTrade(Request $request, P2PTrade $trade, CancelTradeAction $action)
    {
        try {
            $trade = $action->execute($request->user(), $trade);
            $trade->load(['advertisement.bankAccount.bank', 'seller', 'buyer', 'currency', 'bankAccount.bank']);

            return ApiResponse::success(['message' => 'Trade cancelled', 'data' => $trade]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function disputeTrade(DisputeTradeRequest $request, P2PTrade $trade, DisputeTradeAction $action)
    {
        try {
            $trade = $action->execute($request->user(), $trade, $request->reason);
            $trade->load(['advertisement.bankAccount.bank', 'seller', 'buyer', 'currency', 'bankAccount.bank']);

            return ApiResponse::success(['message' => 'Trade disputed successfully', 'data' => $trade]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
