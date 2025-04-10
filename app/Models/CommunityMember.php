<?php

namespace App\Models;

use App\Models\Members\Subscriptions\MemberSubscriptions;
use App\Models\Members\Subscriptions\MemberSubscriptionsTransactions;
use App\Support\Traits\BelongsToCommunity;
use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommunityMember extends Model
{
    use BelongsToCommunity;
    use BelongsToUser;

    public $table = 'community_members';

    protected $fillable = [
        'community_id',
        'user_id',
        'member_id',
        'role',
        'access',
        'monthly_point',
        'weekly_point',
        'point',
        'level',
        'subscription_id'
    ];

    public const NEEDED_POINTS = [
        'level_1' => 0,
        'level_2' => 5,
        'level_3' => 20,
        'level_4' => 50,
        'level_5' => 150,
        'level_6' => 300,
        'level_7' => 500,
        'level_8' => 1000,
        'level_9' => 2500,
        'level_10' => 5000
    ];

    // access value level
    const ACCESS_BANNED = -3;
    const ACCESS_REMOVED = -2;
    const ACCESS_DECLINE = -1;
    const ACCESS_PENDING = 0;
    const ACCESS_ALLOWED = 1;
    const ACCESS_MANAGER = 2;

    // role value level
    const ROLE_MEMBER = 'member';
    const ROLE_MODERATOR = 'moderator';
    const ROLE_ADMIN = 'admin';
    const ROLE_OWNER = 'owner';

    public function subscription(): HasOne
    {
        return $this->hasOne(MemberSubscriptions::class, 'id', 'subscription_id');
    }

    /**
     * @return BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(CommunityGroups::class, 'community_group_members', 'member_id', 'group_id')
            ->withTimestamps();
    }

    public function payment_methods(): HasMany
    {
        return $this->hasMany(PaymentMethodMarketplace::class, 'member_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MemberSubscriptionsTransactions::class, 'member_id', 'id');
    }

    /**
     * Get member ids per access status
     *
     * @param int $communityId
     * @return array
     */
    public static function getMemberIds(int $communityId): array
    {
        $banned = [];
        $pending = [];
        $allowed = [];
        $admin = [];
        $moderator = [];

        $members = self::where(['community_id' => $communityId])->get();
        foreach ($members as $member) {
            if ($member->access === self::ACCESS_BANNED) {
                $banned[] = $member->id;
            } else if ($member->role === self::ROLE_MEMBER && $member->access === self::ACCESS_PENDING) {
                $pending[] = $member->id;
            } else if ($member->role === self::ROLE_MEMBER && $member->access === self::ACCESS_ALLOWED) {
                $allowed[] = $member->id;
            } else if (($member->role === self::ROLE_ADMIN || $member->role === self::ROLE_OWNER) && $member->access === self::ACCESS_ALLOWED) {
                $allowed[] = $member->id;
                $admin[] = $member->id;
            } else if ($member->role === self::ROLE_MODERATOR && $member->access === self::ACCESS_ALLOWED) {
                $allowed[] = $member->id;
                $moderator[] = $member->id;
            }
        }

        return [
            'banned' => array_unique($banned),
            'pending' => array_unique($pending),
            'allowed' => array_unique($allowed),
            'admin' => array_unique($admin),
            'moderator' => array_unique($moderator)
        ];
    }

    /**
     * Check whether member is admin or not
     *
     * @param ?string $role
     * @return bool
     */
    public static function isAdmin(?string $role): bool
    {
        return self::ROLE_OWNER === $role || self::ROLE_ADMIN === $role;
    }

    /**
     * Check whether member is moderator or not
     *
     * @param ?string $role
     * @return bool
     */
    public static function isModerator(?string $role): bool
    {
        return self::ROLE_MODERATOR === $role;
    }

    /**
     * Check whether member is manager or not
     *
     * @param ?string $role
     * @return bool
     */
    public static function isManager(?string $role): bool
    {
        return self::isAdmin($role) || self::isModerator($role);
    }

    /**
     * Check whether member is allowed or not
     *
     * @param int $access
     * @return bool
     */
    public static function isAllowed(int $access): bool
    {
        return self::ACCESS_ALLOWED === $access;
    }

    /**
     * Check whether member is allowed or pending
     *
     * @param int $access
     * @return bool
     */
    public static function isAllowedOrPending(int $access): bool
    {
        return self::ACCESS_ALLOWED === $access || self::ACCESS_PENDING === $access;
    }

    /**
     * @param int $memberId
     * @return array
     */
    public static function getCommunityIds(int $memberId): array
    {
        $communityIds = self::where(['member_id' => $memberId])
            ->pluck('community_id')
            ->toArray();

        return array_unique($communityIds);
    }

    /**
     * @param int $communityId
     * @param int $userId
     * @return CommunityMember|null
     */
    public static function getMember(int $communityId, int $userId): ?CommunityMember
    {
        return self::where(['community_id' => $communityId, 'user_id' => $userId])->first();
    }

    /**
     * @param int $userId
     * @return array
     */
    public static function getActiveCommunityIds(int $userId): array
    {
        return self::where(['user_id' => $userId])
            ->pluck('community_id')
            ->toArray();
    }
}
