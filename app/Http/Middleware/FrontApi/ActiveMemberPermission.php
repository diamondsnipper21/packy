<?php

namespace App\Http\Middleware\FrontApi;

use App\Models\Community;
use App\Models\CommunityMember;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveMemberPermission
{
    /**
     * Check if member has permission for community.
     *
     * @param  Request  $request
     * @param  Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $communityId = $request->communityId ?? '';

        $community = Community::where(['id' => $communityId])->first();
        if (!$community) {
            return response()->json([
                'success' => false,
                'message' => __('Community not found')
            ], 404);
        }

        $member = CommunityMember::where(['community_id' => $communityId, 'user_id' => $request->user->id])
            ->whereIn('access', [CommunityMember::ACCESS_ALLOWED, CommunityMember::ACCESS_PENDING])
            ->first();

        if (!$member && $community->privacy === Community::PRIVACY_PRIVATE) {
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
