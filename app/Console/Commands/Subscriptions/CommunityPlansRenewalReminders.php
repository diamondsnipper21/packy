<?php

namespace App\Console\Commands\Subscriptions;

use App\Services\PlanService;
use Illuminate\Console\Command;

class CommunityPlansRenewalReminders extends Command
{
    /**
     * @var string
     */
    protected $signature = 'command:community-plans-renewal-reminder';

    /**
     * @var string
     */
    protected $description = 'Send an email reminder to users 3 days before renewals';

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param PlanService $planService
     * @return void
     */
    public function handle(PlanService $planService): void
    {
        $planService->sendReminderEmails(3);
    }
}
