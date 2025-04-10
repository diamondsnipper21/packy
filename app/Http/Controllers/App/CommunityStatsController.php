<?php

namespace App\Http\Controllers\App;

use App\Models\Community;
use App\Services\CommunityStatsService;
use Illuminate\Http\Request;

class CommunityStatsController extends AppController
{
    private CommunityStatsService $communityStatsService;

    /**
     * @param CommunityStatsService $communityStatsService
     */
    public function __construct(CommunityStatsService $communityStatsService)
    {
        $this->communityStatsService = $communityStatsService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function get(Request $request): array
    {
        $memberId = $request->member->id;
        $community = $request->community;
        $communityId = $community->id ?? 0;

        $community = Community::find($communityId);
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        return [
            'success' => true,
            'data' => $this->communityStatsService->getStatsByCommunity($communityId)
        ];
    }
}