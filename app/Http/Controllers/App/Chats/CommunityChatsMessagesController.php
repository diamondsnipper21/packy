<?php

namespace App\Http\Controllers\App\Chats;

use App\Events\ChatMessageSent;
use App\Helpers\TextHelper;
use App\Http\Controllers\App\AppController;
use App\Models\Chat;
use App\Models\ChatBlock;
use App\Models\Community;
use App\Models\Medias;
use App\Models\Notification;
use App\Services\LoggerService;
use App\Services\MediaService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class CommunityChatsMessagesController extends AppController
{
    /**
     * Saves a new chat message
     *
     * @param Request $request
     * @param NotificationService $notificationService
     * @param MediaService $mediaService
     * @return array
     */
    public function save(Request $request, NotificationService $notificationService, MediaService $mediaService): array
    {
        $communityId = $request->communityId ?? 0;
        $memberId = $request->memberId ?? 0;
        $fromId = $request->fromId ?? 0;
        $toId = $request->toId ?? 0;
        $medias = $request->medias ?? [];

        $content = $request->content ?? '';
        $mentionedMembers = $request->mentionedMembers ?? [];

        $community = Community::find($communityId);
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        $block = ChatBlock::checkBlockStatus($fromId, $toId);
        if ($block) {
            return ['success' => false, 'message' => __('This message cannot be sent.')];
        }

        try {
            $chat = new Chat();
            $chat->community_id = $communityId;
            $chat->from_id = $fromId;
            $chat->to_id = $toId;
            if ($content) {
                $chat->content = TextHelper::insertMention($content, $mentionedMembers);
            }
            $chat->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        // Generate notifications for mentioned members
        if (!empty($mentionedMembers)) {
            $summary = substr($content, 0, 250);
            $summary = TextHelper::insertMention($summary, $mentionedMembers);
            $notificationService->generateForMention($communityId, $memberId, Notification::OT_MENTION_IN_CHAT, $chat->id, $summary, $content, $mentionedMembers);
        }

        foreach ($medias as $media) {
            $mediaService->createMedia($media, Medias::OWNER_CHAT, $chat->id);
        }

        event(new ChatMessageSent($chat->id, $fromId, $toId));

        return [
            'success' => true,
            'message' => Chat::getChatDetailMessage($chat->id)
        ];
    }

    /**
     * Get a chat message
     *
     * @param Request $request
     * @return array
     */
    public function get(Request $request): array
    {
        return [
            'success' => true,
            'message' => Chat::getChatDetailMessage($request->id)
        ];
    }

    /**
     * Mark all chat message to a user as read
     *
     * @param Request $request
     * @return array
     */
    public function markAllAsRead(Request $request): array
    {
        $user = auth()->user();
        if (!$user) {
            return ['success' => false, 'message' => __('User not found.')];
        }

        try {
            Chat::where('community_id', $request->communityId)
                ->where('to_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => date('Y-m-d H:i:s')]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true];
    }
}
