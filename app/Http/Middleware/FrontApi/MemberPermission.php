<?php

namespace App\Http\Middleware\FrontApi;

use App\Models\Community;
use App\Models\CommunityMember;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberPermission
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
        $memberId = $request->memberId ?? '';

        $community = Community::where(['id' => $communityId])->first();
        if (!$community) {
            return response()->json(['success' => false, 'message' => __('Community not found')], 404);
        }

        $member = CommunityMember::where(['community_id' => $communityId, 'id' => $memberId])->first();
        if (!$member || $request->user->id !== $member->user_id) {
            return response()->json([
                'success' => false,
                'message' => __('You don\'t have permission.')
            ], 403);
        }

        $request->merge(['community' => $community]);

        return $next($request);
    }
}
