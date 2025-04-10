<?php

namespace App\Services;

use App\Models\CommunityMember;
use App\Models\CommunityMemberSetting;
use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\ElementLike;
use App\Models\Notification;

/**
 * Class NotificationService
 *
 * @package App\Services
 */
class NotificationService
{
    /**
     * Generate new notification for likes action
     *
     * @param int $elemType
     * @param object $elem
     * @param int $memberId
     * @param int $status
     * @return void
     */
    public function makeNotificationForLikes(int $elemType, object $elem, int $memberId, int $status): void
    {
        $type = '';
        if ($status == 1) {
            if ($elemType === ElementLike::POST) {
                $type = Notification::OT_LIKE_TO_POST;
            } else if ($elemType === ElementLike::COMMENT) {
                $type = Notification::OT_LIKE_TO_COMMENT;
            }
        }

        $member = CommunityMember::find($memberId);
        if (!$member) {
            return;
        }

        if (!empty($type)) {
            try {
                $notification = new Notification();
                $notification->community_id = $elem->community_id;
                $notification->user_id = $member->user_id;
                $notification->object_type = $type;
                $notification->object_id = $elem->id;
                $notification->owner_id = $elem->member_id;
                $notification->summary = substr($elemType == ElementLike::POST ? $elem->title : $elem->content, 0, 250);
                $notification->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }
    }

    /**
     * Generate new notification for comment action
     *
     * @param CommunityPost $post
     * @param int $communityId
     * @param int $memberId
     * @param int $ownerMemberId
     * @param CommunityPostComment $comment
     * @return void
     */
    public function generateForComment(CommunityPost $post, int $communityId, int $memberId, int $ownerMemberId, CommunityPostComment $comment): void
    {
        if ($comment->parent_id) {
            $type = Notification::OT_REPLY_TO_COMMENT;
            $objectId = $comment->parent_id;
        } else {
            $type = Notification::OT_REPLY_TO_POST;
            $objectId = $post->id;
        }

        $member = CommunityMember::find($memberId);
        if (!$member) {
            return;
        }

        try {
            $notification = new Notification();
            $notification->community_id = $communityId;
            $notification->user_id = $member->user_id;
            $notification->object_type = $type;
            $notification->object_id = $objectId;
            $notification->owner_id = $ownerMemberId;
            $notification->summary = substr($type == Notification::OT_REPLY_TO_COMMENT ? $comment->content : $post->title, 0, 250);
            $notification->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }

    /**
     * Remove wrong notifications
     *
     * @return void
     */
    public function removeWrongNotifications(): void
    {
        $notifications = Notification::whereIn('object_type', [
            Notification::OT_LIKE_TO_POST, 
            Notification::OT_DISLIKE_TO_POST, 
            Notification::OT_REPLY_TO_POST,
            Notification::OT_LIKE_TO_COMMENT,
            Notification::OT_DISLIKE_TO_COMMENT,
            Notification::OT_REPLY_TO_COMMENT
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        if (!empty($notifications)) {
            foreach ($notifications as $notification) {
                if (in_array($notification->object_type, [Notification::OT_REPLY_TO_POST, Notification::OT_LIKE_TO_POST, Notification::OT_DISLIKE_TO_POST])) {
                    $post = CommunityPost::find($notification->object_id);
                    if (!empty($post)) {
                        $parentOwnerId = $post->member_id;
                        if ($parentOwnerId === $notification->user_id) {
                            $notification->delete();
                        }
                    }
                } else if (in_array($notification->object_type, [Notification::OT_REPLY_TO_COMMENT, Notification::OT_LIKE_TO_COMMENT, Notification::OT_DISLIKE_TO_COMMENT])) {
                    $comment = CommunityPostComment::find($notification->object_id);
                    if (!empty($comment)) {
                        $parentOwnerId = $comment->member_id;
                        if ($parentOwnerId === $notification->user_id) {
                            $notification->delete();
                        }
                    }
                }
            }
        }
    }

    /**
     * Generate new notification for mention action
     *
     * @param int $communityId
     * @param int $memberId
     * @param string $objectType
     * @param int $objectId
     * @param string $summary
     * @param string $content
     * @param array $mentionedMembers
     * 
     * @return void
     */
    public function generateForMention(
        int $communityId, 
        int $memberId, 
        string $objectType, 
        int $objectId, 
        string $summary, 
        string $content, 
        array $mentionedMembers
    ): void {
        $member = CommunityMember::find($memberId);
        if (!$mentionedMembers) {
            return;
        }

        $notificationData = [];
        foreach ($mentionedMembers as $mentionedMember) {
            $ownerId = (int)$mentionedMember['id'];
            $mentionTag = trim($mentionedMember['tag']);

            $mentionTagInclude = false;
            if (strpos($content, $mentionTag) !== false) {
                $mentionTagInclude = true;
            }

            $current_date_time = date('Y-m-d H:i:s');

            if ($memberId !== $ownerId && $mentionTagInclude) {
                $notificationData[] = [
                    'community_id' => $communityId,
                    'user_id' => $member->user_id,
                    'object_type' => $objectType,
                    'object_id' => $objectId,
                    'owner_id' => $ownerId,
                    'summary' => $summary,
                    'created_at' => $current_date_time,
                    'updated_at' => $current_date_time
                ];
            }
        }

        Notification::insertOrIgnore($notificationData);
    }
}
