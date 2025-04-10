<?php

namespace App\Http\Controllers\App\Members;

use App\Events\NotificationCreated;
use App\Exceptions\Jobs\SendEmailNotificationForJoinRequest;
use App\Exceptions\Jobs\SendAutoDmForJoinRequest;
use App\Http\Controllers\App\AppController;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Members\Subscriptions\MemberSubscriptionsCancelRequests;
use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\Notification;
use App\Services\LoggerService;
use App\Services\MemberService;
use Illuminate\Http\Request;

class CommunityMembersController extends AppController
{
    private MemberService $memberService;
    
    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function get(Request $request): array
    {
        $memberFilter = $request->memberFilter ?? MemberService::ALL_FILTER;
        $searchFilter = $request->searchFilter ?? '';
        $page = $request->page ?? 0;

        $community = Community::where(['id' => $request->communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        return [
            'success' => true,
            'paginatedMembers' => $this->memberService->getPaginatedMembers($community, $memberFilter, $page, $searchFilter),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function join(Request $request): array
    {
        $community = Community::where(['id' => $request->communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        $incubateurStart = $request->incubateurStart ?? false;

        $accessValue = CommunityMember::ACCESS_PENDING;
        if ($incubateurStart) {
            $accessValue = CommunityMember::ACCESS_ALLOWED;
        }

        $userId = session()->has('user_id') ? session('user_id') : $request->userId;

        $addMember = $this->memberService->addUserToCommunity($community->id, $userId, $accessValue);
        if ($addMember['success'] !== true) {
            return ['success' => false];
        }

        return [
            'success' => true,
            'member' => $this->memberService->getMember($community->id, $userId),
            'members' => $this->memberService->getAllMembers($community->id),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function leave(Request $request): array
    {
        $community = Community::where(['id' => $request->communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        $communityHasMember = CommunityMember::where([
            'community_id' => $community->id,
            'id' => $request->memberId,
        ])->first();

        if (!$communityHasMember) {
            return ['success' => false, 'message' => __('Member not found')];
        }

        if ($communityHasMember->subscription_id) {
            // if the member cancel his subscription, we make a cancel request but the member keeps access
            // until the end of the current billing period
            $cancelImmediately = false;

            $cancelRequest = MemberSubscriptionsCancelRequests::where([
                'community_id' => $community->id,
                'member_id' => $communityHasMember->id,
                'subscription_id' => $communityHasMember->subscription_id,
            ])->first();

            if ($cancelRequest) {
                // the member already asks for a cancel request and confirms to leave the community now
                $cancelImmediately = true;
                $cancelRequest->delete();
            } else {
                MemberSubscriptionsCancelRequests::create([
                    'community_id' => $community->id,
                    'member_id' => $communityHasMember->id,
                    'subscription_id' => $communityHasMember->subscription_id,
                ]);
            }

            $this->memberService->removeMemberFromCommunity(
                $community->id,
                $communityHasMember->id,
                $cancelImmediately
            );

            return [
                'success' => true,
                'member' => $this->memberService->getMember($community->id, $communityHasMember->user_id),
                'members' => $this->memberService->getAllMembers($community->id),
                'message' => __('Your subscription will be ended at the end of your current billing period.'),
            ];
        } else {
            $this->memberService->removeMemberFromCommunity(
                $community->id,
                $communityHasMember->id
            );

            return [
                'success' => true,
                'member' => $this->memberService->getMember($community->id, $communityHasMember->user_id),
                'members' => $this->memberService->getAllMembers($community->id),
                'message' => __('You are no longer a member of this community.'),
            ];
        }
    }

    /**
     * @todo - lot of duplication with decline -> to refact
     *
     * @param Request $request
     * @return array|false[]
     */
    public function approve(Request $request): array
    {
        $communityId = $request->communityId ?? 0;
        $adminId = $request->adminId ?? 0;
        $memberId = $request->memberId ?? 0;

        $community = Community::where(['id' => $communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        $communityMember = CommunityMember::where(['community_id' => $community->id, 'id' => $memberId])->first();
        if (!$communityMember) {
            return ['success' => false, 'message' => __('Member not found')];
        }

        $addMember = $this->memberService->addUserToCommunity(
            $communityId,
            $communityMember->user_id,
            CommunityMember::ACCESS_ALLOWED
        );

        if ($addMember['success'] !== true) {
            return ['success' => false];
        }

        Notification::generateForJoin($community, $adminId, $memberId, Notification::OT_APPROVED_TO_JOIN);
        event(new NotificationCreated($communityId, $adminId, $memberId, Notification::OT_APPROVED_TO_JOIN));

        /*
        $memberIds = CommunityMember::getMemberIds($communityId);
        $paginatedMembers = CommunityMember::whereIn('id', $memberIds['pending'])
            ->with('user')
            ->orderBy('last_activity', 'desc')
            ->simplePaginate(5);
        */

        dispatch (new SendEmailNotificationForJoinRequest(
            $communityId,
            $memberId,
            CommunityMember::ACCESS_ALLOWED,
            $communityMember->user->name
        ))->onQueue('send-email-for-join-request');

        // Generate auto dm for join request
        dispatch (new SendAutoDmForJoinRequest(
            $communityId,
            $memberId
        ))->onQueue('send-auto-dm-for-join-request');

        return [
            'success' => true,
            'paginatedMembers' => $this->memberService->getPaginatedMembers($community, MemberService::PENDING_FILTER),
            'members' => $this->memberService->getAllMembers($communityId),
        ];
    }

    /**
     * @todo - lot of duplication from approve -> to refact
     *
     * @param Request $request
     * @return array
     */
    public function decline(Request $request): array
    {
        $communityId = $request->communityId ?? 0;
        $memberId = $request->memberId ?? 0;

        $community = Community::where(['id' => $communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        $communityMember = CommunityMember::where(['community_id' => $community->id, 'id' => $memberId])->first();
        if (!$communityMember) {
            return ['success' => false, 'message' => __('Member not found')];
        }

        $this->memberService->declineMemberRequest($community->id, $memberId);

        if ($request->shareNotify) {
            Notification::generateForJoin($community, $request->adminId, $memberId, Notification::OT_DECLINED_TO_JOIN);
            event(new NotificationCreated($community->id, $request->adminId, $memberId, Notification::OT_DECLINED_TO_JOIN));
        }

        $memberIds = CommunityMember::getMemberIds($community->id);
        $paginatedMembers = CommunityMember::whereIn('id', $memberIds['pending'])
            ->with('user')
            ->simplePaginate(5);

        dispatch (new SendEmailNotificationForJoinRequest(
            $community->id,
            $memberId,
            CommunityMember::ACCESS_DECLINE,
            $communityMember->user->name
        ))->onQueue('send-email-for-join-request');

        return [
            'success' => true,
            'paginatedMembers' => $paginatedMembers,
            'members' => $this->memberService->getAllMembers($community->id),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function remove(Request $request): array
    {
        $communityMember = CommunityMember::where(['community_id' => $request->communityId, 'id' => $request->memberId])->first();
        if (!$communityMember) {
            return ['success' => false, 'message' => __('Member not found')];
        }

        $this->memberService->removeMemberFromCommunity($communityMember->community_id, $communityMember->id);

        return [
            'success' => true,
            'members' => $this->memberService->getAllMembers($communityMember->community_id),
        ];
    }

    /**
     * Save member
     *
     * @param Request $request
     * @return array
     */
    public function store(Request $request): array
    {
        $communityId = $request->communityId ?? 0;
        $userId = $request->user_id ?? 0;
        $role = $request->role ?? CommunityMember::ROLE_MEMBER;

        $community = Community::where(['id' => $communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        try {
            $communityHasMember = CommunityMember::firstOrNew([
                'community_id' => $communityId,
                'user_id' => $userId,
            ]);
            $communityHasMember->role = $role;
            $communityHasMember->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        return [
            'success' => true,
            'members' => $this->memberService->getAllMembers($communityId),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function ban(Request $request): array
    {
        $communityId = $request->communityId ?? 0;
        $memberId = $request->memberId ?? 0;

        $communityMember = CommunityMember::where(['community_id' => $communityId, 'id' => $memberId])->first();
        if (!$communityMember) {
            return ['success' => false, 'message' => __('Member not found')];
        }

        $access = CommunityMember::ACCESS_BANNED;
        if ($communityMember['access'] === CommunityMember::ACCESS_BANNED) {
            $access = CommunityMember::ACCESS_ALLOWED;
        }

        if ($access === CommunityMember::ACCESS_BANNED) {
            $deleteBanChecked = $communityMember['deleteBanChecked'] ?? 0;
            if ($deleteBanChecked) {
                // Delete 14 day's posts and comments
                $timeLimit = date('Y-m-d', strtotime('-14 days')) . ' 00:00:00';
                $postIds = CommunityPost::where([
                    'community_id' => $communityId,
                    'user_id' => $communityMember['user_id'],
                ])
                ->where('created_at', '>=', $timeLimit)
                ->pluck('id')
                ->toArray();

                CommunityPost::whereIn('id', $postIds)->delete();
                CommunityPostComment::whereIn('post_id', $postIds)->delete();
            }
        }

        $this->memberService->addUserToCommunity(
            $communityId,
            $communityMember->user_id,
            $access
        );

        return [
            'success' => true,
            'members' => $this->memberService->getAllMembers($communityId),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function cancelSubscription(Request $request): array
    {
        $communityId = $request->communityId ?? 0;

        $community = Community::where(['id' => $communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        $member = $request->selectedMember ?? null;
        if (!$member) {
            return ['success' => false, 'message' => __('Member not found')];
        }

        try {
            $this->memberService->removeMemberFromCommunity($community->id, $member['id'], false);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        return [
            'success' => true,
            'members' => $this->memberService->getAllMembers($community->id),
            'message' => __('Subscription cancelled'),
        ];
    }
}