<?php

namespace App\Http\Middleware\FrontApi;

use App\Models\Community;
use App\Models\CommunityMember;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMemberPermission
{
    /**
     * Check if member is admin of community.
     *
     * @param  Request  $request
     * @param  Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $communityId = $request->communityId ?? 0;

        $community = Community::where(['id' => $communityId])->first();
        if (!$community) {
            return response()->json([
                'success' => false,
                'message' => __('Community not found')
            ], 404);
        }

        $member = CommunityMember::where(['community_id' => $communityId, 'user_id' => $request->user->id])->first();
        if (!$member || !CommunityMember::isManager($member->role)) {
            return response()->json([
                'success' => false,
                'message' => __('You don\'t have permission.')
            ], 403);
        }

        $request->merge(['community' => $community]);
        $request->merge(['member' => $member]);

        return $next($request);
    }
}
