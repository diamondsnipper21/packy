<?php

namespace App\Console\Commands\Subscriptions;

use App\Models\Community;
use App\Models\CommunityPlan;
use App\Services\LoggerService;
use Illuminate\Console\Command;

class CommunityPlansCheckStatus extends Command
{
    /**
     * @var string
     */
    protected $signature = 'command:community-plans-check-status';

    /**
     * @var string
     */
    protected $description = 'Check status for each community';

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $communities = Community::where(['status' => Community::STATUS_PUBLISHED])
            ->whereNotIn('user_id', [1,2,5,6,704])
            ->get();

        foreach ($communities as $community)
        {
            $activePlanFound = false;
            foreach ($community->plans as $plan) {
                if (in_array($plan->status, [CommunityPlan::STATUS_ACTIVE, CommunityPlan::STATUS_TRIALING])) {
                    $activePlanFound = true;
                }
            }

            if ($activePlanFound === true) {
                continue;
            }

            try {
                $community->status = Community::STATUS_INACTIVE;
                $community->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
            echo sprintf('No active plan found for community #%s -> status updated to inactive.', $community->id) . PHP_EOL;
        }
    }
}
