<?php

namespace App\Domains\P2P\Actions;

use App\Domains\P2P\Models\P2PTrade;
use App\Enum\TradeStatus;
use App\Mail\P2P\TradePaidMail;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Mail;

class MarkTradePaidAction
{
    /**
     * @throws Exception
     */
    public function execute(User $user, P2PTrade $trade, string $paymentProofUrl): P2PTrade
    {
        if ($trade->status !== TradeStatus::PENDING) {
            throw new Exception('Trade is not in a pending state.', 400);
        }

        if ($trade->buyer_id !== $user->id) {
            throw new Exception('Only the buyer can mark the trade as paid.', 403);
        }

        $trade->status = TradeStatus::PAID;
        $trade->payment_proof = $paymentProofUrl;
        $trade->save();

        Mail::to($trade->seller->email)->queue(new TradePaidMail($trade));

        $currencySymbol = $trade->currency?->symbol ?? 'crypto';
        send_notification(
            $trade->seller,
            'P2P Payment Made',
            'The buyer has marked the trade as paid for '.crypto_format($trade->crypto_amount)." {$currencySymbol}.",
            'p2p_trade_paid',
            ['trade_id' => $trade->id]
        );

        return $trade;
    }
}
