<?php

namespace App\Console\Commands\Migrate;

use Illuminate\Console\Command;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Services\MemberService;

class CancelSubscriptionHandler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:cancel-subscription-handler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handler for active subscription cancelling operation.';

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
     * @param MemberService $memberService
     * @return void
     */
    public function handle(MemberService $memberService): void
    {
        $community = Community::getIncubateurCommunity();
        if (!$community) {
            return;
        }

        $memberIds = CommunityMember::where(['community_id' => $community->id])
            ->whereIn('access', [CommunityMember::ACCESS_ALLOWED, CommunityMember::ACCESS_PENDING])
            ->pluck('member_id')
            ->toArray();

        foreach ($memberIds as $memberId) {
            $memberService->checkIncubateurCommunityMembership($memberId->user_id);
        }
    }
}
