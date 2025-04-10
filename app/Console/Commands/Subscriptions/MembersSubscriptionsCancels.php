<?php

namespace App\Console\Commands\Subscriptions;

use App\Services\Members\MemberSubscriptionService;
use Illuminate\Console\Command;

class MembersSubscriptionsCancels extends Command
{
    /**
     * @var string
     */
    protected $signature = 'command:members-subscription-cancels';

    /**
     * @var string
     */
    protected $description = 'Treat members subscription cancels requests';

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param MemberSubscriptionService $memberSubscriptionService
     * @return void
     */
    public function handle(MemberSubscriptionService $memberSubscriptionService): void
    {
        $memberSubscriptionService->treatCancelsRequests();
    }
}
