<?php

namespace App\Http\Controllers\App\Members;

use App\Models\ApiKey;
use App\Models\CommunityMemberSetting;
use App\Models\CommunityMember;
use App\Models\Community;
use App\Models\CommunityGroups;
use App\Services\MemberService;
use App\Services\LoggerService;
use App\Http\Controllers\App\AppController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * Class MembersController
 *
 * @package App\Http\Controllers\App
 */
class MembersController extends AppController
{
    /**
     * Get Member Settings
     *
     * @param Request $request
     * @param MemberService $memberService
     * @return JsonResponse
     */
    public function view(Request $request, MemberService $memberService): JsonResponse
    {
        $community = Community::where('url', $request->communityUrl)->first();
        if (!$community) {
            return response()->json(['success' => false, 'message' => __('Community not found')], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $memberService->getCommunityMemberInfo($community->id)
        ]);
    }

    /**
     * Store Member Settings
     *
     * @param Request $request
     * @return array
     */
    public function store(Request $request): array
    {
        $community = $request->community;
        $member = $request->member;
        $input = $request->all();

        $memberSetting = CommunityMemberSetting::updateOrCreate(
            [
                'member_id' => $member->id,
                'community_id' => $community->id
            ],
            [
                'admin_announce' => $input['admin_announce'],
                'event_reminder' => $input['event_reminder'],
                'likes' => $input['likes'],
                'reply' => $input['reply'],
                'popular_interval' => $input['popular_interval'],
                'unread_interval' => $input['unread_interval'],
            ]
        );

        return [
            'success' => true,
            'data' => $memberSetting,
            'message' => __('Notification settings updated!')
        ];
    }

    /**
     * Generate Api Key
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateApiKey(Request $request): JsonResponse
    {
        $memberId = $request->memberId ?? '';
        $community = $request->community;

        $member = CommunityMember::where([
            'community_id' => $community->id,
            'id' => $memberId
        ])->first();

        if (empty($member) || !CommunityMember::isManager($member->role)) {
            return response()->json(['success' => false, 'message' => __('You don\'t have permission.')], 403);
        }

        try {
            $existApiKey = false;
            do {
                $key = 'pk_' . Str::random(32);
                $existApiKey = ApiKey::where(['api_key' => $key]);
            } while (empty($existApiKey));

            $apiKey = ApiKey::firstOrNew([
                'community_id' => $community->id,
                'member_id' => $memberId
            ]);

            // Generate API Key
            $apiKey->api_key = $key;
            $apiKey->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $apiKey
        ]);
    }

    /**
     * Get community member info as CommunityMember
     *
     * @param Request $request
     * @return CommunityMember|null
     */
    public function getCommunityMember(Request $request): ?CommunityMember
    {
        return CommunityMember::where([
            'community_id' => $request->communityId,
            'id' => $request->memberId,
        ])->first();
    }

    /**
     * Get communities that member is joined
     *
     * @param Request $request
     * @return array
     */
    public function communities(Request $request): array
    {
        $members = CommunityMember::where('user_id', $request->user->id)
            ->whereIn('access', [CommunityMember::ACCESS_ALLOWED])
            ->with('community')
            ->get();

        $communities = [];
        foreach ($members as $member) {
            $member->community->access = $member->access;
            $communities[] = $member->community;
        }

        return [
            'success' => true,
            'data' => $communities
        ];
    }

    /**
     * Get assignable members and groups
     *
     * @param Request $request
     * @param MemberService $memberService
     * @return array
     */
    public function assignable(Request $request, MemberService $memberService): array
    {
        $communityId = $request->community->id;
        $role = CommunityMember::ACCESS_ALLOWED;

        $members = DB::table('community_members')
            ->join('users', 'community_members.user_id', '=', 'users.id')
            ->select(['community_members.id', 'users.firstname', 'users.lastname', 'users.photo'])
            ->where('community_members.community_id', '=', DB::raw($communityId))
            ->where('community_members.access', '=', DB::raw($communityId))
            ->where(DB::raw('LOWER(users.firstname)'), 'like', '%' .  strtolower($request->input('query')) . '%')
            ->orWhere(DB::raw('LOWER(users.lastname)'), 'like', '%' . strtolower($request->input('query')) . '%')
            ->get()->toArray();

        $options = [];
        foreach ($members as $member) {
            $options[] = [
                'id' => $member->id,
                'name' => $member->user->firstname . ' ' . $member->user->lastname,
                'avatar' => $memberService->getMemberAvatarUrl($member)
            ];
        }

        $groups = CommunityGroups::where('community_id', $request->communityId)
            ->where(DB::raw('LOWER(name)'), 'like', '%' . strtolower($request->input('query')) . '%')
            ->get();

        foreach ($groups as $item) {
            $options[] = [
                'id' => 'group_' . $item->id,
                'name' => $item->name,
                'avatar' => ''
            ];
        }
        return [
            'success' => true,
            'data' => $options
        ];
    }

    /**
     * Get Member summary
     *
     * @param Request $request
     * @param MemberService $memberService
     * @return JsonResponse
     */
    public function getMember(Request $request, MemberService $memberService): JsonResponse
    {
        $communityId = $request->communityId ?? 0;
        $memberId = $request->memberId ?? 0;

        $communityMember = CommunityMember::where(['id' => $memberId, 'community_id' => $communityId])->first();
        if (!$communityMember) {
            return response()->json(['success' => false, 'message' => __('Member not found')], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $memberService->getMember((int)$communityId, (int)$communityMember->user_id, false)
        ]);
    }
}
