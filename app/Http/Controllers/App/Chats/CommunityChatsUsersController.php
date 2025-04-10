<?php

namespace App\Http\Controllers\App\Chats;

use App\Http\Controllers\App\AppController;
use App\Models\Chat;
use App\Models\ChatBlock;
use App\Models\User;
use App\Services\LoggerService;
use Illuminate\Http\Request;

class CommunityChatsUsersController extends AppController
{
    /**
     * @param Request $request
     * @return array
     */
    public function get(Request $request): array
    {
        $chatUserFilter = $request->chatUserFilter ?? '';
        $searchFilter = $request->searchFilter ?? '';

        $user = auth()->user();
        if (!$user) {
            return ['success' => false, 'message' => __('User not found.')];
        }

        $userId = $user->id;
        $relatedUserIds = Chat::getRelatedChatUserIds($user->id, $chatUserFilter);
        $blockedUserIds = ChatBlock::getBlockedChatUserIds($user->id);
        $userIds = array_diff($relatedUserIds, $blockedUserIds);

        if ($searchFilter) {
            $query = User::query();
            $query->where(function ($q) use ($searchFilter, $userId) {
                return $q->where([
                    ['id', '!=', $userId],
                    ['email', 'LIKE', '%' . $searchFilter . '%']
                ])
                    ->orWhere([
                        ['id', '!=', $userId],
                        ['firstname', 'LIKE', '%' . $searchFilter . '%']
                    ])
                    ->orWhere([
                        ['id', '!=', $userId],
                        ['lastname', 'LIKE', '%' . $searchFilter . '%']
                    ]);
            });

            $users = $query->whereIn('id', $relatedUserIds)
                ->with('member')
                ->get();
        } else {
            $users = User::where([['id', '!=', $user->id]])
                ->whereIn('id', $userIds)
                ->with('member')
                ->get();
        }

        return [
            'success' => true,
            'users' => $users,
            'unreadChatsCnt' => count(Chat::getUnreadChatUserIds($user->id)),
            'blockedUserIds' => $blockedUserIds
        ];
    }

    /**
     * Blocks a user on the chat
     *
     * @param Request $request
     * @return array
     */
    public function block(Request $request): array
    {
        try {
            $chatBlock = new ChatBlock();
            $chatBlock->from_id = $request->fromId;
            $chatBlock->to_id = $request->toId;
            $chatBlock->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
        
        return [
            'success' => true,
            'message' => __('Blocked successfully.')
        ];
    }

    /**
     * Unblocks a user on the chat
     *
     * @param Request $request
     * @return array
     */
    public function unblock(Request $request): array
    {
        try {
            ChatBlock::where([
                'from_id' => $request->fromId,
                'to_id' => $request->toId
            ])->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return [
            'success' => true,
            'message' => __('Unblocked successfully.')
        ];
    }
}
