<?php

namespace App\Http\Controllers\App\SuperAdmin;

use App\Models\Community;
use App\Services\CommunityStatsService;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SuperAdminStatsController extends SuperAdminAppController
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
     * Displays super admin dashboard
     *
     * @return View
     */
    public function view(): View
    {
        return view('dashboard-super-admin');
    }

    /**
     * Returns stats data for super admin
     *
     * @param Request $request
     * @return array
     */
    public function get(Request $request): array
    {
        $url = $request->url ?? '';
        if (!$url) {
            return ['success' => false, 'message' => __('Community url should be provided')];
        }

        $community = Community::where(['url' => $url])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        return [
            'success' => true,
            'stats' => $this->communityStatsService->getStatsByCommunity($community->id)
        ];
    }
}
