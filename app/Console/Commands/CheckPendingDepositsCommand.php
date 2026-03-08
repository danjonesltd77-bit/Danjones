<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Wallet\Actions\CheckPendingDepositsAction;
use Throwable;

class CheckPendingDepositsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:check-pending-deposits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Polls the blockchain gateway to check if any pending deposits have been fully confirmed.';

    /**
     * Execute the console command.
     */
    public function handle(CheckPendingDepositsAction $action): int
    {
        $this->info('Checking for pending deposit confirmations...');

        try {
            $action->execute();
        } catch (Throwable $e) {
            $this->error('Failed checking pending deposits: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Pending deposits checked successfully.');

        return self::SUCCESS;
    }
}
