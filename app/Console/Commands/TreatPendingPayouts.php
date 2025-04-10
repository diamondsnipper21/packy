<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use App\Services\PayoutService;
use App\Services\StripeService;
use Illuminate\Console\Command;

class TreatPendingPayouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:treat-pending-payouts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Treat and transfer all pending payouts to the appropriate recipients';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(BillingService $billingService): void
    {
        $billingService->treatPendingPayouts();
        $this->info('All pending payouts have been treated.');
    }
}
