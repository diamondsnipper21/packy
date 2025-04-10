<?php

namespace App\Http\Controllers\App\Chats;

use App\Http\Controllers\App\AppController;
use App\Models\Chat;
use App\Models\Community;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommunityChatsController extends AppController
{
    /**
     * @param Request $request
     * @return array
     */
    public function getChat(Request $request): array
    {
        $communityId = $request->communityId ?? 0;
        $chatId = $request->chatId ?? 0;

        $community = Community::where(['id' => $communityId])->first();
        if (empty($community)) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        $chat = Chat::where(['id' => $chatId])->first();
        if (empty($chat)) {
            return ['success' => false, 'message' => __('Chat not found')];
        }

        return [
            'success' => true,
            'chat' => $chat
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function get(Request $request): array
    {
        $fromId = $request->fromId ?? 0;
        $toId = $request->toId ?? 0;

        $currentUserId = session('user_id');
        if ($currentUserId === $toId) {
            $userId = $fromId;
        } else if ($currentUserId === $fromId) {
            $userId = $toId;
        } else {
            return ['success' => false, 'message' => __('You cannot see this chat.')];
        }

        $messages = Chat::getChatDetailMessages($fromId, $toId);
        $user = User::where(['id' => $userId])->first();

        return [
            'success' => true,
            'messages' => $messages,
            'user' => $user
        ];
    }

    /**
     * Handler for chat request
     *
     * @param int $id
     * @return View|RedirectResponse
     */
    public function chatHandler(int $id): View|RedirectResponse
    {
        $invalid = false;
        if ($id) {
            $invalid = true;
        }

        $url = '';
        $userId = 0;
        $fromId = 0;
        $toId = 0;

        if (!$invalid) {
            $chat = Chat::find($id);
            if ($chat) {
                $fromId = $chat->from_id;
                $toId = $chat->to_id;

                $community = Community::find($chat->community_id);
                if ($community) {
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
