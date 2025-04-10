<?php

namespace App\Console\Commands\Subscriptions;

use App\Services\Members\MemberSubscriptionService;
use Illuminate\Console\Command;

class MembersSubscriptionsRenewalReminders extends Command
{
    /**
     * @var string
     */
    protected $signature = 'command:members-subscription-renewal-reminder';

    /**
     * @var string
     */
    protected $description = 'Send an email reminder to members 3 and 2 days before renewals';

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
        $memberSubscriptionService->sendReminderEmails(3);
        $memberSubscriptionService->sendReminderEmails(2);
    }
}
