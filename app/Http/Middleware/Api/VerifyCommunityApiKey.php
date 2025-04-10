<?php

namespace App\Http\Middleware\Api;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use GrahamCampbell\Throttle\Facades\Throttle;

class VerifyCommunityApiKey
{
    /**
     * Verify community and member from api key
     *
     * @var Request $request
     * @var Closure $next
     * 
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');
        $apiKeyItem = ApiKey::where(['api_key' => $apiKey])
            ->with('community')
            ->with('member')
            ->first();

        if (empty($apiKeyItem)) {
            return response()->json(['success' => false, 'message' => __('You don\'t have permission.')], 403);
        }

        $maxRequests = $apiKeyItem->max_requests ?? config('app.api_max_requests');
        $throttler = Throttle::get($request, $maxRequests, 1);
        Throttle::attempt($request);
        if (!$throttler->check()) {
            return response()->json(['success' => false, 'message' => __('Too many requests.')], 429);
        }

        $member = CommunityMember::where([
            'community_id' => $apiKeyItem->community_id,
            'id' => $apiKeyItem->member_id,
        ])->first();

        if (empty($member) || !CommunityMember::isManager($member->role)) {
            return response()->json(['success' => false, 'message' => __('You don\'t have permission.')], 403);
        }

        if($apiKeyItem->community->status != Community::STATUS_PUBLISHED){
            return response()->json(['success' => false, 'message' => __('Community not found.')], 404);
        }

        $request->merge(['member' => $apiKeyItem->member]);
        $request->merge(['community' => $apiKeyItem->community]);

        return $next($request);
    }
}
