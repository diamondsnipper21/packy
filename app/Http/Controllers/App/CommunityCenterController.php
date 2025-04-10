<?php

namespace App\Http\Controllers\App;

use App\Enum\StripeAccountStatusEnum;
use App\Models\Chat;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityNotificationsSettings;
use App\Models\Notification;
use App\Models\StripeAccount;
use App\Models\User;
use App\Services\LoggerService;
use App\Services\MemberService;
use App\Services\StripeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommunityCenterController extends AppController
{
    public const PREDEFINED_URLS = [
        'c', // client api prefix
        'reset-password',
        'health-check-status',
        'auth',
        'join',
        'unsubscribe-digest',
        'chat',
        'unread-notification',
        'legal'
    ];

    /**
     * Community Center Dashboard
     */
    public function index(string $url)
    {
        $community = Community::where(['url' => request()->route('url')])->first();
        if (!$community) {
            return redirect()->route('home');
        }

        // if some tabs are disabled from settings, we redirect to community main page.
        $routeName = request()->route()->getName();
        if ($routeName === 'community_classrooms' && $community->tab_classrooms === 0 ||
            $routeName === 'community_calendar' && $community->tab_calendar === 0 ||
            $routeName === 'community_leaderboard' && $community->tab_leaderboard === 0
        ) {
            return redirect()->route('community_index', ['url' => request()->route('url')]);
        }

        $auth = false;
        $userId = session('user_id');

        // set user locale for translations
        if ($userId) {
            $user = User::find(session('user_id'));
            if ($user) {
                $auth = true;
                if ($user->language) {
                    app()->setLocale($user->language);
                }
            }
        }

        return view('dashboard', [
            'auth' => $auth,
            'userId' => $userId
        ]);
    }

    /**
     * Get community info
     *
     * @param Request $request
     * @param MemberService $memberService
     * @return array
     */
    public function getCommunity(Request $request, MemberService $memberService): array
    {
        $userId = session('user_id');

        $url = $request->url ?? '';
        if (!$url) {
            return ['success' => false, 'message' => __('Community url should be provided')];
        }

        $community = Community::where(['url' => $url])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        $communityId = $community->id;

        $user = null;
        $members = null;
        $membersCount = 0;
        $wolfeoLoginUrl = null;
        $unreadNotificationsCnt = 0;
        $unreadChatsCnt = 0;

        $member = $memberService->getMember($communityId);

        if ($userId) {
            // Get customer info
            $user = User::getUserInfo();
            if ($user && $user->id_client === 6746026629) {
                $wolfeoUser = User::where(['email' => $user->email])->first();
                if ($wolfeoUser && $wolfeoUser->password) {
                    $wolfeoLoginUrl = 'https://' . env('APP_DOMAIN') . '/login?t=' . hash('sha256', $wolfeoUser->password) . '&d=' . hash('sha256', $wolfeoUser->updated_at) . '&i=' . $wolfeoUser->id_client;
                }
            }

            if ($user && $member) {
                $unreadChatsCnt = count(Chat::getUnreadChatUserIds($user->id));
                if ($user->member) {
                    $unreadNotificationsCnt = Notification::where([
                        'community_id' => $communityId,
                        'owner_id' => $member->id
                    ])
                        ->whereNull('read_at')
                        ->count();
                }
            }
        }

        $access = $member->access ?? CommunityMember::ACCESS_DECLINE;

        $membersCount = CommunityMember::where(['community_id' => $communityId, 'access' => CommunityMember::ACCESS_ALLOWED])->count();
        if ($community->privacy === Community::PRIVACY_PUBLIC ||
            $access === CommunityMember::ACCESS_ALLOWED) {
            $members = $memberService->getAllMembers($communityId);
        }

        $notify = null;
        if (session('flash-message')) {
            $notify = session('flash-message');
            session()->forget('flash-message');
        }

        $stripeConnected = false;
        $stripeLoginLink = null;

        $stripeAccount = StripeAccount::where(['user_id' => session('user_id')])->first();
        if ($stripeAccount && $stripeAccount->status === StripeAccountStatusEnum::STATUS_ENABLED) {
            $stripeConnected = true;

            $stripeService = new StripeService();
            $createLoginLink = $stripeService->createLoginLink($stripeAccount->account_id);
            if ($createLoginLink) {
                $stripeLoginLink = $createLoginLink->url;
            }
        }

        return [
            'success' => true,
            'user' => $user,
            'members' => $members,
            'membersCount' => $membersCount,
            'wolfeoLoginUrl' => $wolfeoLoginUrl,
            'neededPoints' => CommunityMember::NEEDED_POINTS,
            'notify' => $notify,
            'stripeConnected' => $stripeConnected,
            'stripeLoginLink' => $stripeLoginLink,
            'unreadNotificationsCnt' => $unreadNotificationsCnt,
            'unreadChatsCnt' => $unreadChatsCnt
        ];
    }

    /**
     * Save temp community
     *
     * @param Request $request
     * @return array
     */
    public function saveTempCommunity(Request $request): array
    {
        // not used anymore

        return ['success' => true];
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function viewProfile(Request $request): View|RedirectResponse
    {
        if (!$request->tag) {
            return redirect()->to('/');
        }

        $user = User::where(['tag' => $request->tag])->first();
        if (!$user) {
            return redirect()->to('/');
        }

        return view('dashboard', [
            'auth' => (bool) session('user_id'),
            'action' => 'view-profile',
            'userTag' => $user->tag
        ]);
    }

    /**
     * Get profile
     *
     * @param Request $request
     * @param MemberService $memberService
     * @return array
     */
    public function getProfile(Request $request, MemberService $memberService): array
    {
        if (!$request->tag) {
            return ['success' => false, 'message' => __('Tag should be provided!')];
        }

        $member = CommunityMember::where(['tag' => $request->tag])->first();
        if (!$member) {
            return ['success' => false, 'message' => __('Tag is not valid')];
        }

        return [
            'success' => true,
            'user' => User::getUserInfo(),
            'member' => $memberService->getMember($member->community->id, $member->user->id)
        ];
    }

    /**
     * Handler for unsubscribe digest request
     *
     * @param string $url
     * @return View|RedirectResponse
     */
    public function unsubscribeDigest(string $url): View|RedirectResponse
    {
        $invalid = false;
        if (!$url) {
            $invalid = true;
        }

        if (!$invalid) {
            $community = Community::where(['url' => $url])->first();
            if (!$community) {
                $invalid = true;
            }
        }

        $auth = auth()->check() === true;
        if (!$invalid && !$auth) {
            $invalid = true;
        }

        if ($invalid) {
            if ($community) {
                return redirect()->to('/' . $url);
            } else {
                return redirect()->to('/');
            }
        }

        return view('dashboard', [
            'auth' => $auth,
            'action' => 'unsubscribe-digest',
            'communityUrl' => $url
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function saveTabs(Request $request): array
    {
        try {
            Community::where(['id' => $request->communityId])->update([
                'tab_classrooms' => $request->tabClassrooms,
                'tab_calendar' => $request->tabCalendar,
                'tab_leaderboard' => $request->tabLeaderboard
            ]);
        } catch (\Exception $e) {
            \Log::error(['saveTabs exception', $e->getMessage()]);
            return ['success' => false];
        }

        return ['success' => true];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function saveNotifications(Request $request): array
    {
        try {
            foreach ($request->notifications as $type => $value) {
                $params = [
                    'community_id' => $request->communityId,
                    'type' => $type
                ];

                if ($value === true) {
                    $settings = CommunityNotificationsSettings::firstOrNew($params);
                    $settings->save();
                } else {
                    CommunityNotificationsSettings::where($params)->delete();
                }
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        return [
            'success' => true,
            'message' => __('Notification updated successfully')
        ];
    }
}
