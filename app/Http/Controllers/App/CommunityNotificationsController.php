<?php

namespace App\Http\Controllers\App;

use App\Models\Community;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\LoggerService;

class CommunityNotificationsController extends AppController
{
    /**
     * Get notifications
     *
     * @param Request $request
     * @return array
     */
    public function get(Request $request): array
    {
        $communityId = $request->communityId ?? 0;
        $memberId = $request->memberId ?? 0;
        $filter = $request->filter ?? Notification::FILTER_ALL;

        $community = Community::where(['id' => $communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        return [
            'success' => true,
            'notifications' => Notification::getNotificationsByCommunityId($communityId, $memberId, $filter)
        ];
    }

    /**
     * Mark notification as read
     *
     * @param Request $request
     * @return array
     */
    public function markAsRead(Request $request): array
    {
        $communityId = $request->communityId ?? 0;
        $memberId = $request->memberId ?? 0;
        $id = $request->id ?? 0;
        $type = '';
        $redirectUrl = '';

        if ($id) {
            $notification = Notification::find($id);
            if (!empty($notification) && $notification->read_at === null) {
                try {
                    $notification->read_at = date('Y-m-d H:i:s');
                    $notification->save();
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }

            $type = $notification->object_type ?? '';
            $redirectUrl = Notification::getRedirectUrl($id);
        } else {
            try {
                Notification::where([
                    'community_id' => $communityId,
                    'owner_id' => $memberId,
                ])
                    ->whereNull('read_at')
                    ->update([
                        'read_at' => date('Y-m-d H:i:s')
                    ]);
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return [
            'success' => true,
            'type' => $type,
            'redirectUrl' => $redirectUrl
        ];
    }

    /**
     * Handler for unread notification request
     *
     * @param int $id
     */
    public function unreadNotificationHandler(int $id)
    {
        $invalid = false;
        if (empty($id)) {
            $invalid = true;
        }

        $url = '';
        if (!$invalid) {
            $notification = Notification::find($id);
            if (!empty($notification)) {
                $community = Community::find($notification->community_id);
                if (!empty($community)) {
                    $url = $community->url;
                } else {
                    $invalid = true;
                }
            } else {
                $invalid = true;
            }
        }

        if (!$invalid) {
            $memberId = $notification->owner_id;
            $currentUser = User::getUserInfo();
            if (!empty($currentUser->member)) {
                if ($memberId !== $currentUser->member->id) {
                    $invalid = true;
                }
            } else {
                $invalid = true;
            }
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
            'action' => 'unread-notification',
            'communityUrl' => $url,
            'userId' => $id
        ]);
    }
}
