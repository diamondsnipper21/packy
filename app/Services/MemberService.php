<?php

namespace App\Services;

use App\Models\Members\Subscriptions\MemberSubscriptions;
use App\Models\Members\Subscriptions\MemberSubscriptionsCancelRequests;
use App\Models\Chat;
use App\Models\Notification;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityMemberSetting;
use App\Models\CommunityPlan;
use App\Models\ElementLike;
use App\Models\InviteUserTokens;
use App\Models\Referrals;
use App\Models\RewardLevel;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Jobs\SendEmailNotificationForJoinRequest;

class MemberService
{
    public const ALL_FILTER = 'all';
    public const MODERATOR_FILTER = 'moderator';
    public const ADMIN_FILTER = 'admin';
    public const ALLOWED_FILTER = 'allowed';
    public const ONLINE_FILTER = 'online';
    public const PENDING_FILTER = 'pending';
    public const DECLINED_FILTER = 'declined';
    public const BANNED_FILTER = 'banned';

    private BillingService $billingService;
    private UserService $userService;

    public function __construct(BillingService $billingService, UserService $userService)
    {
        $this->billingService = $billingService;
        $this->userService = $userService;
    }

    /**
     * @param int $communityId
     * @param int $memberId
     * @return void
     */
    public function declineMemberRequest(int $communityId, int $memberId): void
    {
        $communityMember = CommunityMember::where([
            'id' => $memberId,
            'community_id' => $communityId
        ])->first();

        if (!$communityMember) {
            return;
        }

        try {
            $communityMember->access = CommunityMember::ACCESS_DECLINE;
            $communityMember->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }

    /**
     * @param int $communityId
     * @param int $memberId
     * @param bool $cancelImmediately
     * @return void
     */
    public function removeMemberFromCommunity(int $communityId, int $memberId, bool $cancelImmediately = true): void
    {
        $community = Community::find($communityId);
        if (empty($community)) {
            return;
        }

        $communityMember = CommunityMember::where([
            'community_id' => $communityId,
            'id' => $memberId
        ])->first();

        if (empty($communityMember)) {
            return;
        }

        // Creator cannot be removed from community
        if ($community->user_id === $communityMember->user_id) {
            return;
        }

        // we don't delete banned accesses because we want to know who has been banned forever
        if ($cancelImmediately === true &&
            $communityMember->access !== CommunityMember::ACCESS_BANNED) {
            $communityMember->access = CommunityMember::ACCESS_REMOVED;
            $communityMember->save();
        }

        $this->removeMemberSubscription($communityMember, $cancelImmediately);
    }

    /**
     * @param CommunityMember $communityHasMember
     * @param bool $cancelImmediately
     * @return void
     */
    private function removeMemberSubscription(CommunityMember $communityHasMember, bool $cancelImmediately = true): void
    {
        if (!$communityHasMember->subscription_id) {
            return;
        }

        try {
            $stripeService = new StripeService(
                secretKey: null,
                connectedAccount: $communityHasMember->community->user->stripeAccount->account_id
            );

            $stripeService->cancelSubscription(
                $communityHasMember->subscription->stripe_subscription_id,
                'Cancelled from membership panel',
                $cancelImmediately
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }

        if ($cancelImmediately === false) {
            try {
                $request = MemberSubscriptionsCancelRequests::firstOrNew([
                    'community_id' => $communityHasMember->community->id,
                    'member_id' => $communityHasMember->id,
                    'subscription_id' => $communityHasMember->subscription->id
                ]);
                $request->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }
    }

    /**
     * Checks if a user can be a member of incubateur community
     * When the community is canceled and if the CREATOR has no other communities, it should be removed from `INCUBATEUR` community
     *
     * @param int $userId
     * @return void
     */
    public function checkIncubateurCommunityMembership(int $userId): void
    {
        $incubateurCommunity = Community::getIncubateurCommunity();
        if (!$incubateurCommunity) {
            return;
        }

        $activeCommunityCount = Community::getActiveCommunityCount($userId);
        if ($activeCommunityCount) {
            $this->addUserToCommunity($incubateurCommunity->id, $userId, CommunityMember::ACCESS_ALLOWED);
        } else {
            $this->removeUserFromCommunity($incubateurCommunity->id, $userId);
        }
    }

    /**
     * @param int $communityId
     * @param int $userId
     * @return void
     */
    private function removeUserFromCommunity(int $communityId, int $userId): void
    {
        $member = CommunityMember::where([
            'community_id' => $communityId,
            'user_id' => $userId
        ])
            ->whereIn('access', [CommunityMember::ACCESS_ALLOWED])
            ->first();

        if (!$member) {
            return;
        }

        $this->removeMemberFromCommunity($communityId, $member->id);
    }

    /**
     * Adds a member to a community
     *
     * @param int $communityId
     * @param int $userId
     * @param int $access
     * @param string $role
     * @param int|null $subscriptionId
     * @return array
     */
    public function addUserToCommunity(int $communityId, int $userId, int $access = 0, string $role = CommunityMember::ROLE_MEMBER, int $subscriptionId = null): array
    {
        if ($role === CommunityMember::ROLE_OWNER) {
            $community = Community::where(['id' => $communityId])->first();
        } else {
            $community = Community::where(['id' => $communityId, 'status' => Community::STATUS_PUBLISHED])->first();
        }

        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        $communityMember = CommunityMember::firstOrNew([
            'community_id' => $communityId,
            'user_id' => $userId
        ]);

        if ($communityMember->access === CommunityMember::ACCESS_BANNED) {
            return ['success' => false, 'message' => __('You cannot join this community because you have been banned from it.')];
        }

        try {
            $communityMember->access = $access;
            if ($subscriptionId) {
                $communityMember->subscription_id = $subscriptionId;
            }
            $communityMember->role = $role;
            $communityMember->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        try {
            $setting = CommunityMemberSetting::firstOrNew([
                'community_id' => $communityId,
                'member_id' => $communityMember->id
            ]);
            $setting->popular_interval = CommunityMemberSetting::POPULAR_INTERVAL_OPT_WEEKLY;
            $setting->unread_interval = CommunityMemberSetting::UNREAD_INTERVAL_OPT_HOURLY;
            $setting->likes = 1;
            $setting->reply = 1;
            $setting->admin_announce = 1;
            $setting->event_reminder = 1;
            $setting->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        return [
            'success' => true,
            'member' => $communityMember
        ];
    }

    /**
     * @param array $request
     * @param User $user
     * @return string
     */
    public function addUserToCommunityByToken(array $request, User $user): string
    {
        $inviteToken = $request['inviteToken'] ?? null;

        // check if token existis
        $token = InviteUserTokens::where(['token' => $inviteToken])
            ->with('user')
            ->first();

        if (!$token) {
            return '';
        }

        // check if community exists
        $communityId = $token->community_id ?: $request['communityId'];
        $community = Community::find($communityId);
        if (!$community) {
            return '';
        }

        // add refer record
        try {
            $refer = Referrals::firstOrNew([
                'referral_id' => $token->user->id,
                'user_id' => $user->id
            ]);
            $refer->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return '';
        }

        // add user to community
        try {
            $this->userService->updateUser($user, $request);
            $this->addUserToCommunity(
                communityId: $community->id,
                userId: $user->id,
                access: CommunityMember::ACCESS_ALLOWED
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return '';
        }

        session()->forget('inviteToken');

        return $community->url;
    }

    /**
     * Attach start/last chat message to user items
     *
     * @param Collection $users
     * @param int $curId
     * @return Collection
     */
    public function attachStartLastMessage(Collection $users, int $curId): Collection
    {
        foreach ($users as $key => $user) {
            $user->startChat = $this->getStartChatForUser($curId, $user->id);
            $user->lastChat = $this->getLastChatForUser($curId, $user->id);
            $users[$key] = $user;
        }

        return $users;
    }

    /**
     * @todo - move to Chat model
     *
     * Get User's last chat message
     *
     * @param int $curId
     * @param int $userId
     * @return Chat
     */
    private function getLastChatForUser(int $curId, int $userId): Chat
    {
        $chat = Chat::where(['from_id' => $userId, 'to_id' => $curId])
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$chat) {
            $chat = Chat::where(['from_id' => $userId, 'to_id' => $curId])
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$chat) {
            $chat = Chat::where(['from_id' => $curId, 'to_id' => $userId])
                ->orderBy('created_at', 'desc')
                ->first();
        }

        return $chat;
    }

    /**
     * @todo - move to Chat model
     *
     * Get User's start chat message
     *
     * @param int $fromId
     * @param int $toId
     * @return Chat
     */
    private function getStartChatForUser(int $fromId, int $toId): Chat
    {
        return Chat::where(function ($query) use ($fromId, $toId) {
            $query->where('from_id', $fromId)
                  ->where('to_id', $toId);
        })->orWhere(function ($query) use ($fromId, $toId) {
            $query->where('from_id', $toId)
                  ->where('to_id', $fromId);
        })->orderBy('created_at', 'asc')
            ->first();
    }

    /**
     * Get community member detail
     *
     * @param int $communityId
     * @param int|null $userId
     * @return CommunityMember|null
     */
    public function getCommunityMemberInfo(int $communityId, int $userId = null): ?CommunityMember
    {
        if ($userId === null) {
            $userId = session('user_id');
        }

        $query = CommunityMember::where(['community_id' => $communityId, 'user_id' => $userId])
            ->with('subscription')
            ->with('subscription.cancel')
            ->with('subscription.payment_method')
            ->with('subscription.transactions');

        if (auth()->check() === true) {
            $query->with('groups');
        }

        $member = $query->first();
        if (!$member) {
            return $member;
        }

        $user = $member->user;
        if (!empty($user)) {
            try {
                $user->last_activity = date('Y-m-d H:i:s');
                $user->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }

        $memberAccess = $this->getAccess($communityId, $userId);
        $member->access = $memberAccess;

        $member->role = self::getRole($communityId, $userId);
        $member->points = 0;
        $member->weeklyPoints = 0;
        $member->monthlyPoints = 0;
        $member->level = 1;

        if (CommunityMember::isAllowed($memberAccess)) {
            $member->points = $member->point;
            $member->weeklyPoints = $member->weekly_point;
            $member->monthlyPoints = $member->monthly_point;
            $member->level = $this->getMemberLevel($communityId, $member->points);
        }

        // Get unread chat count
        $unreadChatUserIds = Chat::getUnreadChatUserIds($member->user_id);

        // Get unread notifications
        $unreadNotifications = Notification::getUnreadNotificationsByMemberId($communityId, $userId);

        $member->unreadChatsCnt = count($unreadChatUserIds);
        $member->unreadNotificationsCnt = count($unreadNotifications);

        // Get count of active community
        $activeCommunityIds = CommunityMember::getActiveCommunityIds($userId);
        $member->activeCommunityCnt = count($activeCommunityIds);

        // Get count of active subscriptions for community
        $member->activeSubscriptionCnt = CommunityPlan::getCountActiveSubscriptions($activeCommunityIds);
        $member->incubateurMemberExist = $this->isUserIncubateurCommunityMember($userId);
        
        $member->vatRate = $this->billingService->getVatRateByCountry($member->user->country);

        return $member;
    }

    /**
     * Get Member's avatar url
     *
     * @param Object $member
     * @return string
     */
    public function getMemberAvatarUrl(object $member): string
    {
        if ($member->photo) {
            return $member->photo;
        }
        return 'https://www.gravatar.com/avatar/' . md5($member->user->firstname . $member->user->lastname . $member->id) . '?s=48&d=identicon';
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function isUserIncubateurCommunityMember(int $userId): bool
    {
        $incubateurMemberExist = false;

        $community = Community::getIncubateurCommunity();
        if ($community) {
            $incubateurMemberExist = $this->checkCommunityMemberExist($community->id, $userId);
        }

        return $incubateurMemberExist;
    }

    /**
     * @param int $communityId
     * @param int $userId
     * @return bool
     */
    public function checkCommunityMemberExist(int $communityId, int $userId): bool
    {
        $member = CommunityMember::where(['community_id' => $communityId, 'user_id' => $userId])
            ->whereIn('access', [CommunityMember::ACCESS_ALLOWED])
            ->first();

        return (bool)$member;
    }

    /**
     * @param string $base
     * @param string $type
     * @return bool
     * @throws \Exception
     */
    public function calculateMembersPoints(string $base, string $type): bool
    {
        $baseDate = new \DateTime($base);
        $baseDate = $baseDate->format('Y-m-d H:i:s');

        $limit = 10;
        $offset = 0;
        $result = true;
        while ($result) {
            $records = ElementLike::where('status', 1)
                ->where('created_at', '>', $baseDate)
                ->groupBy('community_id', 'element_owner_id')
                ->havingRaw('COUNT(element_owner_id) > 0')
                ->select('community_id', 'element_owner_id', DB::raw('COUNT(element_owner_id) AS point'))
                ->offset($offset)
                ->limit($limit)
                ->get();
            if (!count($records)) break;

            $memberIds = [];
            foreach ($records as $item) {
                $memberIds[] = $item->element_owner_id;
                $query = CommunityMember::where(['community_id' => $item->community_id, 'id' => $item->element_owner_id]);
                switch ($type) {
                    case 'day':
                        $member = $query->first();
                        if ($member) {
                            $member->point = $member->point + $item->point;
                            $member->level = $this->getMemberLevel($member->community_id, $member->point);
                            $member->save();
                        }
                        break;
                    case 'week':
                        $query->update(['weekly_point' => $item->point]);
                        break;
                    case 'month':
                        $query->update(['monthly_point' => $item->point]);
                        break;
                }
            }

            sleep(1);
            $offset = $offset + $limit;
        }

        return true;
    }

    /**
     * Get member's level
     *
     * @param int $points
     * @param int $communityId
     * @return int
     */
    private function getMemberLevel(int $communityId, int $points): int
    {
        $levels = RewardLevel::where('community_id', $communityId)->orderBy('goal_point', 'desc')->get();
        foreach ($levels as $key => $level) {
            if ($points >= $level->goal_point){
                return $level->level_number;
            }
        }

        return 1;
    }

    /**
     * @param string $name
     * @return string
     */
    public function generateUniqTag(string $name): string
    {
        $valid = false;
        $tag = '';

        while ($valid === false) {
            $tag = strtolower(str_replace(' ', '', $name)) . mt_rand(10000, 99999);
            $count = User::where('tag', '=', $tag)->count();
            if ($count == 0) {
                $valid = true;
                break;
            }
        }

        return $tag;
    }

    /**
     * Clean member tags
     *
     * @return void
     */
    public function cleanMemberTags(): void
    {
        $users = CommunityMember::all();
        foreach ($users as $user) {
            $tag = ltrim($user->tag, '@');
            if (!$tag) {
                $fullName = trim($user->name);
                $tag = $this->generateUniqTag($fullName);
            }

            try {
                $user->tag = $tag;
                $user->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }
    }

    /**
     * Get member's access
     *
     * @param int $communityId
     * @param int $userId
     * @return null | int
     */
    private function getAccess(int $communityId, int $userId): ?int
    {
        $communityHasMember = CommunityMember::where([
            'community_id' => $communityId,
            'user_id' => $userId,
        ])->first();

        if (empty($communityHasMember)) {
            return CommunityMember::ACCESS_DECLINE;
        }

        return $communityHasMember->access;
    }

    /**
     * Get member's role
     *
     * @param int $communityId
     * @param int|null $userId
     * @return string|null
     */
    public static function getRole(int $communityId, int $userId = null): ?string
    {
        if ($userId === null) {
            $userId = session('user_id');
        }

        $communityMember = CommunityMember::where([
            'community_id' => $communityId,
            'user_id' => $userId,
        ])->first();

        if (empty($communityMember)) {
            return null;
        }

        $role = $communityMember->role;

        return $role;
    }

    /**
     * @param Community $community
     * @param string $memberFilter
     * @param int $page
     * @param string|null $searchFilter
     * @return LengthAwarePaginator
     */
    public function getPaginatedMembers(Community $community, string $memberFilter, int $page = 0, string $searchFilter = null): LengthAwarePaginator
    {
        $memberIds = CommunityMember::getMemberIds($community->id);

        $ids = $memberIds['allowed'];
        if ($memberFilter === self::PENDING_FILTER) {
            $ids = $memberIds['pending'];
        } else if ($memberFilter === self::ADMIN_FILTER) {
            $ids = $memberIds['admin'];
        } else if ($memberFilter === self::MODERATOR_FILTER) {
            $ids = $memberIds['moderator'];
        } else if ($memberFilter === self::BANNED_FILTER) {
            $ids = $memberIds['banned'];
        }

        $query = CommunityMember::query();
        if (auth()->check() === true) {
            $query->select(
                'community_members.*',
                'users.last_activity as last_activity',
                'community_member_subscriptions.status as subscription_status',
                'community_member_subscriptions.next_billing_at as subscription_next_billing_at'
            );
        } else {
            $query->select(
                'users.*',
                'community_members.role as role',
                'community_members.access as access',
                'community_members.updated_at as has_updated_at',
            );
        }

        if ($memberFilter === self::ONLINE_FILTER) {
            $date = new \DateTime();
            $date->modify('-15 minutes');
            $query->where('users.last_activity', '>', $date->format('Y-m-d H:i:s'));
        }

        $query->join('users', 'community_members.user_id', '=', 'users.id')
            ->where('community_members.community_id','=', $community->id)
            ->whereIn('community_members.id', $ids);

        if ($community->owner_show === Community::OWNER_HIDE) {
            $query->whereNotIn('community_members.user_id', [$community->user_id]);
        }

        if ($searchFilter) {
            $query->where(function ($q) use ($searchFilter) {
                $q->where('users.firstname', 'LIKE', "%{$searchFilter}%")
                    ->orWhere('users.lastname', 'LIKE', "%{$searchFilter}%")
                    ->orWhere('users.tag', 'LIKE', "%{$searchFilter}%");
            });
        }

        if (auth()->check() === true) {
            $query->with('user')
                ->with('user.transactions')
                ->with('transactions')
                ->leftJoin('community_member_subscriptions', function ($join) use($community) {
                    $join->on('community_members.id', '=', 'community_member_subscriptions.member_id');
                    $join->where('community_member_subscriptions.community_id','=', $community->id);
                    $join->where('community_member_subscriptions.status','!=', MemberSubscriptions::STATUS_CANCELLED);
                });
        }

        return $query->orderBy('users.last_activity', 'desc')
            ->distinct()
            ->paginate(5, ['*'], 'page', $page);
    }

    /**
     * @todo - duplication with MemberService@getCommunityMemberInfo... ???
     *
     * Get community member information
     *
     * @param int $communityId
     * @param int|null $userId
     * @param bool $registerHistory
     * @return object|null
     */
    public function getMember(int $communityId = 0, int $userId = null, bool $registerHistory = true): ?object
    {
        if ($userId === null) {
            $userId = session('user_id');
        }

        $query = CommunityMember::select('community_members.*')
            ->leftJoin('users', function ($join) use($userId) {
                $join->on('users.id', '=', 'community_members.user_id');
                if ($userId) {
                    $join->where('users.id','=', $userId);
                }
            })
            ->where('community_members.community_id','=', $communityId)
            ->where('users.id', $userId)
            ->with('user');

        if (auth()->check() === true) {
            $query
                ->with('user.paymentMethodsMarketplace')
                ->with('groups');
        }

        $member = $query->first();

        if (!$member) {
            return null;
        }

        // Update last activity of this member
        if ($registerHistory) {
            $user = $member->user;
            if (!empty($user)) {
                try {
                    $user->last_activity = date('Y-m-d H:i:s');
                    $user->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                }
            }
        }

        if ($communityId) {
            $memberAccess = $this->getAccess($communityId, $userId);

            $member->points = 0;
            $member->weeklyPoints = 0;
            $member->monthlyPoints = 0;
            $member->level = 1;

            // Calculate points | level for only allowed member
            if (CommunityMember::isAllowed($memberAccess)) {
                $member->points = $member->point;
                $member->weeklyPoints = $member->weekly_point;
                $member->monthlyPoints = $member->monthly_point;
                $member->level = $this->getMemberLevel($communityId, $member->points);
            }

            $unreadChatUserIds = Chat::getUnreadChatUserIds($member->user_id);
            $unreadNotifications = Notification::getUnreadNotificationsByMemberId($communityId, $member->id);

            $member->unreadChatsCnt = count($unreadChatUserIds);
            $member->unreadNotificationsCnt = count($unreadNotifications);
        }

        $communityHasMember = CommunityMember::where(['community_id' => $communityId, 'user_id' => $userId])
            ->with('subscription.cancel')
            ->with('subscription.payment_method')
            ->first();

        if ($communityHasMember && $communityHasMember->subscription) {
            $member->subscription = $communityHasMember->subscription;
        }

        return $member;
    }

    /**
     * Get all members of community
     *
     * @param int $communityId
     * @return array
     */
    public function getAllMembers(int $communityId = 0): array
    {
        $memberIds = CommunityMember::getMemberIds($communityId);

        $bannedMemberIds = $memberIds['banned'];
        $pendingMemberIds = $memberIds['pending'];
        $allowedMemberIds = $memberIds['allowed'];
        $adminMemberIds = $memberIds['admin'];
        $moderatorMemberIds = $memberIds['moderator'];

        $allMemberIds = array_unique(array_merge($allowedMemberIds, $pendingMemberIds, $bannedMemberIds));

        $community = Community::where(['id' => $communityId])->first();

        $query = CommunityMember::query()
            ->select('community_members.*', 'community_members.updated_at as has_updated_at')
            ->where('community_members.community_id','=', $community->id)
            ->whereIn('community_members.id', $allMemberIds);

        if (auth()->check() === true) {
            $query->with('user');
        }

        $members = $query->distinct()->get();

        $bannedMembers = [];
        $pendingMembers = [];
        $allowedMembers = [];
        $adminMembers = [];
        $moderatorMembers = [];

        if (!empty($members)) {
            foreach ($members as $member) {
                $member->points = 0;
                $member->weeklyPoints = 0;
                $member->monthlyPoints = 0;
                $member->level = 1;

                // Calculate points | level for only allowed member
                if (in_array($member->id, $allowedMemberIds)) {
                    $member->points = $member->point;
                    $member->weeklyPoints = $member->weekly_point;
                    $member->monthlyPoints = $member->monthly_point;
                    $member->level = $this->getMemberLevel($communityId, $member->points);
                }

                if (in_array($member->id, $bannedMemberIds)) {
                    $bannedMembers[] = $member;
                }

                if (in_array($member->id, $pendingMemberIds)) {
                    $pendingMembers[] = $member;
                }

                if (in_array($member->id, $allowedMemberIds)) {
                    $allowedMembers[] = $member;
                }

                if (in_array($member->id, $adminMemberIds)) {
                    $adminMembers[] = $member;
                }

                if (in_array($member->id, $moderatorMemberIds)) {
                    $moderatorMembers[] = $member;
                }
            }
        }

        return [
            'banned' => $bannedMembers,
            'pending' => $pendingMembers,
            'allowed' => $allowedMembers,
            'admin' => $adminMembers,
            'moderator' => $moderatorMembers
        ];
    }
}
