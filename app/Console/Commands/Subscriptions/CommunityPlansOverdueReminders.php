<?php

namespace App\Console\Commands\Subscriptions;

use App\Models\CommunityPlan;
use App\Services\PlanService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CommunityPlansOverdueReminders extends Command
{
    /**
     * @var string
     */
    protected $signature = 'command:community-plans-overdue-reminder';

    /**
     * @var string
     */
    protected $description = 'Send an email reminder to past due plans';

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
        $log = sprintf('Started at %s', Carbon::now()->format('Y-m-d H:i:s')) . PHP_EOL;

        $communityPlans = CommunityPlan::where('status', CommunityPlan::STATUS_PAST_DUE)->get();
        $log .= sprintf('%s plan(s) found', $communityPlans->count()) . PHP_EOL;

        foreach ($communityPlans as $communityPlan) {
            $log .= sprintf('-> plan #%s / community #%s / user #%s',
                    $communityPlan->id,
                    $communityPlan->community->id,
                    $communityPlan->community->user->id
                ) . PHP_EOL;

            $planService->sendReminder($communityPlan);
        }

        $log .= sprintf('Ended at %s', Carbon::now()->format('Y-m-d H:i:s')) . PHP_EOL;

        \Log::channel('reminders')->info($log);
    }
}
