<?php

namespace App\Http\Controllers\App;

use App\Models\Chat;
use App\Models\ChatBlock;
use App\Models\Community;
use App\Models\Medias;
use App\Models\User;
use App\Events\ChatMessageSent;
use App\Services\MediaService;
use App\Services\MemberService;
use Illuminate\Http\Request;

/**
 * Class CommunityChatsController
 *
 * @package App\Http\Controllers\App
 */
class CommunityChatsController extends AppController
{
    /**
     * @param Request $request
     * @param MemberService $memberService
     * @return array
     */
    public function getChatUsers(Request $request, MemberService $memberService): array
    {
        $communityId = $request->communityId ?? 0;
        $chatUserFilter = $request->chatUserFilter ?? '';
        $searchFilter = $request->searchFilter ?? '';

        $user = auth()->user();
        if (!$user) {
            return ['success' => false, 'message' => __('User not found.')];
        }

        $authUserId = $user->id;

        $relatedUserIds = Chat::getRelatedChatUserIds($authUserId, $chatUserFilter);
        $blockedUserIds = ChatBlock::getBlockedChatUserIds($authUserId);
        $userIds = array_diff($relatedUserIds, $blockedUserIds);

        $users = null;
        if (!empty($searchFilter)) {
            $query = User::query();
            $query->where(function ($q) use ($searchFilter, $authUserId) {
                return $q->where([
                    ['id', '!=', $authUserId],
                    ['email', 'LIKE', '%' . $searchFilter . '%']
                ])
                    ->orWhere([
                        ['id', '!=', $authUserId],
                        ['firstname', 'LIKE', '%' . $searchFilter . '%']
                    ])
                    ->orWhere([
                        ['id', '!=', $authUserId],
                        ['lastname', 'LIKE', '%' . $searchFilter . '%']
                    ]);
            });

            $users = $query->whereIn('id', $relatedUserIds)
                ->with('member')
                ->get();
        } else {
            $users = User::where([
                ['id', '!=', $authUserId]
            ])
                ->whereIn('id', $userIds)
                ->with('member')
                ->get();
        }

        $unreadChatUserIds = Chat::getUnreadChatUserIds($authUserId);
        $users = $memberService->attachStartLastMessage($users, $authUserId);

        return [
            'success' => true,
            'users' => $users,
            'unreadChatsCnt' => count($unreadChatUserIds),
            'blockedUserIds' => $blockedUserIds
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getChatDetail(Request $request): array
    {
        $communityId = $request->communityId ?? 0;
        $fromId = $request->fromId ?? 0;
        $toId = $request->toId ?? 0;

        $userId = 0;
        $currentUserId = session('user_id');
        if ($currentUserId === $toId) {
            $userId = $fromId;
        } else if ($currentUserId === $fromId) {
            $userId = $toId;
        } else {
            return ['success' => false, 'message' => __('User not valid.')];
        }

        $messages = Chat::getChatDetailMessages($fromId, $toId);
        $user = User::where(['id' => $userId])->with('member')->first();

        return [
            'success' => true,
            'messages' => $messages,
            'user' => $user
        ];
    }

    /**
     * Save chat message
     *
     * @param Request $request
     * @param MediaService $mediaService
     * @return array
     */
    public function saveChatMessage(Request $request, MediaService $mediaService): array
    {
        $communityId = $request->communityId ?? 0;
        $fromId = $request->fromId ?? 0;
        $toId = $request->toId ?? 0;
        $content = $request->content ?? '';
        $medias = $request->medias ?? [];

        $community = Community::find($communityId);
        if (empty($community)) {
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
            if (!empty($content)) {
                $chat->content = $content;
            }
            $chat->save();
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        if (!empty($medias)) {
            foreach ($medias as $media) {
                $mediaService->createMedia($media, Medias::OWNER_CHAT, $chat->id);
            }
        }

        event(new ChatMessageSent($chat->id, $fromId, $toId));

        $chatDetailMessage = Chat::getChatDetailMessage($chat->id);

        return [
            'success' => true,
            'message' => $chatDetailMessage
        ];
    }

    /**
     * Get chat message
     *
     * @param Request $request
     * @return array
     */
    public function getChatMessage(Request $request): array
    {
        $id = $request->id ?? 0;
        $chatDetailMessage = Chat::getChatDetailMessage($id);

        return [
            'success' => true,
            'message' => $chatDetailMessage
        ];
    }

    /**
     * Mark chat all as read
     *
     * @param Request $request
     * @return array
     */
    public function markAllAsRead(Request $request): array
    {
        $communityId = $request->communityId ?? 0;

        $user = auth()->user();
        if (!$user) {
            return ['success' => false, 'message' => __('User not found.')];
        }

        $chats = Chat::where('community_id', $communityId)
            ->where('to_id', $user->id)
            ->whereNull('read_at')
            ->get();

        if (!empty($chats)) {
            foreach ($chats as $chat) {
                try {
                    $chat->read_at = date('Y-m-d H:i:s');
                    $chat->save();
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }
        }

        return ['success' => true];
    }

    /**
     * Block user chat
     *
     * @param Request $request
     * @return array
     */
    public function blockUser(Request $request): array
    {
        $fromId = $request->fromId ?? 0;
        $toId = $request->toId ?? 0;

        if ($fromId && $toId) {
            try {
                $chatBlock = new ChatBlock();
                $chatBlock->from_id = $fromId;
                $chatBlock->to_id = $toId;
                $chatBlock->save();
            } catch (\Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return [
            'success' => true,
            'message' => __('Blocked successfully.')
        ];
    }

    /**
     * Unblock user chat
     *
     * @param Request $request
     * @return array
     */
    public function unblockUser(Request $request): array
    {
        $fromId = $request->fromId ?? 0;
        $toId = $request->toId ?? 0;

        if ($fromId && $toId) {
            try {
                ChatBlock::where([
                    'from_id' => $fromId,
                    'to_id' => $toId
                ])->delete();
            } catch (\Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return [
            'success' => true,
            'message' => __('Unblocked successfully.')
        ];
    }

    /**
     * Handler for chat request
     *
     * @param int $id
     */
    public function chatHandler(int $id)
    {
        $invalid = false;
        if (empty($id)) {
            $invalid = true;
        }

        $url = '';
        $userId = 0;
        $fromId = 0;
        $toId = 0;

        if (!$invalid) {
            $chat = Chat::find($id);
            if (!empty($chat)) {

                $fromId = $chat->from_id;
                $toId = $chat->to_id;

                $community = Community::find($chat->community_id);
                if (!empty($community)) {
                    $url = $community->url;
                } else {
                    $invalid = true;
                }
            } else {
                $invalid = true;
            }
        }

        $currentUserId = session('user_id');
        if ($currentUserId === $toId) {
            $userId = $fromId;
        } else if ($currentUserId === $fromId) {
            $userId = $toId;
        } else {
            $invalid = true;
        }

        $auth = false;
        if (auth()->check() === true) {
            $auth = true;
        }

        if (!$invalid && !$auth) {
            $invalid = true;
        }

        if ($invalid) {
            if (!empty($community)) {
                return redirect()->to('/' . $url);
            } else {
                return redirect()->to('/');
            }
        }

        return view('dashboard', [
            'auth' => $auth,
            'action' => 'chat',
            'communityUrl' => $url,
            'userId' => $userId
        ]);
    }
}
