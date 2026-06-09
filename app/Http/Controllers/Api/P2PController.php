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
use App\Http\Controllers\Controller;
use App\Http\Requests\P2P\DisputeTradeRequest;
use App\Http\Requests\P2P\InitiateTradeRequest;
use App\Http\Requests\P2P\MarkTradePaidRequest;
use App\Http\Requests\P2P\StoreAdvertisementRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class P2PController extends Controller
{
    public function indexAds()
    {
        $ads = P2PAdvertisement::where('is_active', true)
            ->with(['user', 'currency'])
            ->latest()
            ->get();

        return ApiResponse::success(['ads' => $ads]);
    }

    public function myAds(Request $request)
    {
        $ads = $request->user()->p2pAdvertisements()
            ->with(['currency'])
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
            ->with(['advertisement', 'seller', 'buyer', 'currency'])
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

            return ApiResponse::success(['message' => 'Advertisement created successfully', 'data' => $ad], 201);
        } catch (\Exception $e) {

            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function initiateTrade(InitiateTradeRequest $request, InitiateTradeAction $action)
    {
        try {
            $ad = P2PAdvertisement::findOrFail($request->advertisement_id);
            $trade = $action->execute($request->user(), $ad, $request->amount);

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

            return ApiResponse::success(['message' => 'Trade marked as paid', 'data' => $trade]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function completeTrade(Request $request, P2PTrade $trade, CompleteTradeAction $action)
    {
        try {
            $trade = $action->execute($request->user(), $trade);

            return ApiResponse::success(['message' => 'Trade completed and crypto released', 'data' => $trade]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function cancelTrade(Request $request, P2PTrade $trade, CancelTradeAction $action)
    {
        try {
            $trade = $action->execute($request->user(), $trade);

            return ApiResponse::success(['message' => 'Trade cancelled', 'data' => $trade]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function disputeTrade(DisputeTradeRequest $request, P2PTrade $trade, DisputeTradeAction $action)
    {
        try {
            $trade = $action->execute($request->user(), $trade, $request->reason);

            return ApiResponse::success(['message' => 'Trade disputed successfully', 'data' => $trade]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
