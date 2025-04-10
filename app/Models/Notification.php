<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use BelongsToUser;
    use BelongsToCommunity;

    public $table = 'notifications';

    protected $fillable = [
        'community_id',
        'user_id',
        'object_type',
        'object_id',
        'owner_id',
        'summary',
        'read_at'
    ];

    public const OT_LIKE_TO_POST = 'like_to_post';
    public const OT_LIKE_TO_COMMENT = 'like_to_comment';
    public const OT_DISLIKE_TO_POST = 'dislike_to_post';
    public const OT_DISLIKE_TO_COMMENT = 'dislike_to_comment';
    public const OT_REPLY_TO_POST = 'reply_to_post';
    public const OT_REPLY_TO_COMMENT = 'reply_to_comment';
    public const OT_APPROVED_TO_JOIN = 'approved_to_join';
    public const OT_DECLINED_TO_JOIN = 'declined_to_join';
    public const OT_NEW_PAYMENT = 'new_payment';
    public const OT_MENTION_IN_CHAT = 'mention_in_chat';
    public const OT_MENTION_IN_POST = 'mention_in_post';
    public const OT_MENTION_IN_COMMENT = 'mention_in_comment';

    public const FILTER_ALL = 'all';
    public const FILTER_UNREAD = 'unread';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * Generate new notification for approve action
     *
     * @param Community $community
     * @param int $memberId
     * @param int $objectId
     * @param string $objectType
     * @param string|null $description
     * @return void
     */
    public static function generateForJoin(
        Community $community,
        int $memberId,
        int $objectId,
        string $objectType,
        string $description = null
    ): void
    {
        $member = CommunityMember::find($memberId);
        if (!$member) {
            return;
        }

        if ($description === null) {
            $description = substr($community->summary_description, 0, 250);
        }

        try {
            $notification = new Notification();
            $notification->community_id = $community->id;
            $notification->user_id = $member->user_id;
            $notification->object_type = $objectType;
            $notification->object_id = $objectId;
            $notification->owner_id = $objectId;
            $notification->summary = $description;
            $notification->save();
        } catch (\Exception $e) {
            \Log::info(['generateForJoin failed', $e->getMessage()]);
        }
    }

    /**
     * Get redirect url for this notification
     *
     * @param int $id
     * @return string
     */
    public static function getRedirectUrl(int $id): string
    {
        $redirectUrl = '';
        $notification = self::find($id);
        if (!empty($notification)) {
            if ($notification->object_type === self::OT_LIKE_TO_POST || $notification->object_type === self::OT_DISLIKE_TO_POST || $notification->object_type === self::OT_REPLY_TO_POST || $notification->object_type === self::OT_MENTION_IN_POST) {
                $post = CommunityPost::find($notification->object_id);
                $communityId = $post->community_id ?? 0;
                $community = Community::find($communityId);
                if (!empty($community)) {
                    $redirectUrl = $post->path;
                }
            } else if ($notification->object_type === self::OT_LIKE_TO_COMMENT || $notification->object_type === self::OT_DISLIKE_TO_COMMENT || $notification->object_type === self::OT_REPLY_TO_COMMENT || $notification->object_type === self::OT_MENTION_IN_COMMENT) {
                $comment = CommunityPostComment::find($notification->object_id);
                $postId = $comment->post_id ?? 0;
                $post = CommunityPost::find($postId);
                if (!empty($post)) {
                    $communityId = $post->community_id ?? 0;
                    $community = Community::find($communityId);
                    if (!empty($community)) {
                        $redirectUrl = $post->path;
                    }
                }
            }
        }

        return $redirectUrl;
    }

    /**
     * Get notification info
     *
     * @param object $notification
     * @return object
     */
    public static function getNotificationInfo(object $notification): object
    {
        // Get user gravatar
        $gravatar = '';
        if (!empty($notification->user)) {
            if (!empty($notification->user->photo)) {
                $gravatar = $notification->user->photo;
            }

            if (empty($gravatar) && !empty($notification->member->user)) {
                $email = strtolower($notification->member->user->email);
                if (!empty($email)) {
                    $gravatar = 'https://www.gravatar.com/avatar/' . md5($email) . '?s=48&d=identicon';
                }
            }
        }

        if (empty($gravatar)) {
            $gravatar = 'https://wolfeo.me/assets/img/avatars/default.png';
        }

        $notification->gravatar = $gravatar;

        // Get user name
        $name = '';
        if (!empty($notification->user)) {
            if (!empty($notification->user->firstname)) {
                $name .= $notification->user->firstname . ' ';
            }

            if (!empty($notification->user->lastname)) {
                $name .= $notification->user->lastname;
            }

            if (empty($name)) {
                $name = $notification->user->email;
            }
        }

        $notification->name = trim($name);

        return $notification;
    }

    /**
     * Get unread notifications
     *
     * @param int $communityId
     * @param int $memberId
     * @return array
     */
    public static function getUnreadNotificationsByMemberId(int $communityId, int $memberId): array
    {
        $unreadNotifications = [];

        $query = self::query();
        $query->where(['community_id' => $communityId, 'owner_id' => $memberId]);
        $query->whereNull('read_at');
        $unreadNotifications = $query->get()->toArray();
        return $unreadNotifications;
    }

    /**
     * @param int $communityId
     * @param string|null $timeLimit
     * @return Collection
     */
    public static function getUnreadNotitications(int $communityId, string $timeLimit = null): Collection
    {
        $query = self::query()->where(['community_id' => $communityId])
            ->whereNull('read_at');

        if ($timeLimit) {
            $query->where('created_at', '>=', $timeLimit);
        }

        return $query->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @param int $communityId
     * @param int $memberId
     * @param string|null $filter
     * @return Collection
     */
    public static function getNotificationsByCommunityId(int $communityId, int $memberId, string $filter = null): Collection
    {
        $query = self::query()->where([
            'community_id' => $communityId,
            'owner_id' => $memberId,
        ]);

        if ($filter === Notification::FILTER_UNREAD) {
            $query->whereNull('read_at');
        }

        return $query
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
