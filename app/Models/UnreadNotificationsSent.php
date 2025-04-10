<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnreadNotificationsSent extends Model
{
    use BelongsToCommunity;

    public $table = 'unread_notifications_sent';

    protected $fillable = [
        'community_id',
        'member_id',
        'type',
        'notification_id'
    ];

    public const NOTIFICATION_TYPE = 'notification';
    public const CHAT_TYPE = 'chat';

    public function member(): BelongsTo
    {
        return $this->belongsTo(CommunityMember::class, 'member_id', 'id');
    }

    /* @todo - add foreign relation for unread_notifications_sent.notification_id to notifications
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class, 'notification_id', 'id');
    }
    */

    /**
     * Get post ids that already sent for popular interval
     *
     * @param int $communityId
     * @param int $memberId
     * @param string $type
     * @return array
     */
    public static function getSentNotificationIds(int $communityId, int $memberId, string $type): array
    {
        $notificationIds = self::where([
            'community_id' => $communityId,
            'member_id' => $memberId,
            'type' => $type
        ])
        ->pluck('notification_id')
        ->toArray();

        return $notificationIds;
    }
}
