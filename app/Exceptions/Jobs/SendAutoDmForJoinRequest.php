<?php

namespace App\Exceptions\Jobs;

use App\Models\CommunityMember;
use App\Models\User;
use App\Services\ExtensionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAutoDmForJoinRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $communityId;
    public $memberId;

    /**
     * @param $communityId
     * @param $memberId
     * 
     * @return void
     */
    public function __construct(
        $communityId,
        $memberId
    ) {
        $this->communityId = $communityId;
        $this->memberId = $memberId;
    }

    /**
     * @param ExtensionService $extensionService
     * @return void
     */
    public function handle(ExtensionService $extensionService)
    {
        $toUserIds = [];
        $member = CommunityMember::find($this->memberId);
        if (!empty($member)) {
            $user = User::find($member->user_id);
            if (!empty($user)) {
                $toUserIds[] = $member->user_id;
            }
        }

        $response = $extensionService->sendAutoDm($this->communityId, ExtensionService::FOR_JOIN_REQUEST, $toUserIds);
    }
}
