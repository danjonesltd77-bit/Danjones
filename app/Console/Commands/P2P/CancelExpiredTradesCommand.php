<?php

namespace App\Console\Commands\P2P;

use App\Domains\P2P\Actions\CancelTradeAction;
use App\Domains\P2P\Models\P2PTrade;
use App\Enum\TradeStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CancelExpiredTradesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'p2p:cancel-expired-trades';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel P2P trades that have exceeded the 30-minute pending window and refund the seller.';

    /**
     * Execute the console command.
     */
    public function handle(CancelTradeAction $action): int
    {
        $expirationTime = Carbon::now()->subMinutes(30);

        $expiredTrades = P2PTrade::where('status', TradeStatus::PENDING)
            ->where('created_at', '<', $expirationTime)
            ->get();

        if ($expiredTrades->isEmpty()) {
            $this->info('No expired trades found.');

            return 0;
        }

        $this->info("Found {$expiredTrades->count()} expired trade(s). Proceeding with cancellation...");

        foreach ($expiredTrades as $trade) {
            try {
                $action->execute(null, $trade);
                $this->info("Trade #{$trade->id} cancelled successfully.");
            } catch (\Exception $e) {
                $this->error("Failed to cancel trade #{$trade->id}: {$e->getMessage()}");
            }
        }

        $this->info('Expiration cleanup completed.');

        return 0;
    }
}
