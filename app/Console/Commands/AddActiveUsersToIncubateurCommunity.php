<?php

namespace App\Console\Commands;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityPlan;
use App\Services\MemberService;
use Illuminate\Console\Command;

class AddActiveUsersToIncubateurCommunity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:add-active-users-incubateur-community';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     *
     * @param MemberService $memberService
     * @return void
     */
    public function handle(MemberService $memberService): void
    {
        $community = Community::getIncubateurCommunity();
        if (!$community) {
            return;
        }

        $usersIds = [];

        $plans = CommunityPlan::where(['status' => CommunityPlan::STATUS_ACTIVE])
            ->orWhere(['status' => CommunityPlan::STATUS_TRIALING])
            ->get();

        foreach ($plans as $plan) {
            $usersIds[] = $plan->community->user_id;
        }

        $usersIds = array_unique($usersIds);
        foreach ($usersIds as $userId)
        {
            $member = CommunityMember::where(['community_id' => $community->id, 'user_id' => $userId])->first();
            if ($member) {
                echo 'User #' . $userId . ' is already a member of incubateur community -> NEXT.' . PHP_EOL;
                continue;
            }

            // 3. add member to community
            $memberService->addUserToCommunity(
                $community->id,
                $userId,
                CommunityMember::ACCESS_ALLOWED
            );

            echo 'User #' . $userId . ' added to incubateur community.' . PHP_EOL;
        }
    }
}
