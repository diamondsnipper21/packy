<?php

namespace App\Services;

use App\Enum\LangEnum;
use App\Helpers\DatetimeHelper;
use App\Mail\PopularIntervalMail;
use App\Mail\UnreadIntervalMail;
use App\Mail\CommunityTrialPlanReminderMail;
use App\Models\Chat;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityMedia;
use App\Models\CommunityMemberSetting;
use App\Models\CommunityPost;
use App\Models\DigestPostsSent;
use App\Models\ElementLike;
use App\Models\InviteUserTokens;
use App\Models\Medias;
use App\Models\Notification;
use App\Models\UnreadNotificationsSent;
use App\Models\User;
use App\Models\CommunityPlan;
use Illuminate\Support\Facades\Mail;

class CommunityService extends Service
{
    public const USER_INVITE_LOGIN_URL = '%s/auth/join?token=%s&url=%s';
    public const RESET_PASSWORD_URL = '%s/reset-password?token=%s&url=%s';
    public const VIEW_POST_URL = '%s/%s/%s';
    public const JOIN_REQUEST_URL = '%s/%s';
    public const UNSUBSCRIBE_DIGEST_URL = '%s/unsubscribe-digest/%s';
    public const CHAT_URL = '%s/chat/%d';

    public const PREDEFINED_URLS = [
        'c',    // client api prefix
        'api',    // api prefix
        'webhooks',    // api prefix
        'reset-password',
        'health-check-status',
        'auth',
        'join',
        'unsubscribe-digest',
        'chat',
        'unread-notification',
        'legal'
    ];

    private LevelService $levelService;
    private ExtensionService $extensionService;

    public function __construct(LevelService $levelService, ExtensionService $extensionService)
    {
        $this->levelService = $levelService;
        $this->extensionService = $extensionService;
    }

    /**
     * @param int $communityId
     * @param int $memberId
     * @param bool $autoApproval
     * @return array
     */
    public function getInviteShareLink(int $communityId, int $memberId, bool $autoApproval = false): array
    {
        $community = Community::find($communityId);
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        $member = CommunityMember::where(['id' => $memberId])->with('user')->first();
        if (!$member) {
            return ['success' => false, 'message' => __('Member not found.')];
        }

        $user = $member->user;

        $token = InviteUserTokens::generateUniqToken();
        try {
            $row = new InviteUserTokens();
            $row->user_id = $user->id;
            $row->community_id = $community->id;
            $row->token = $token;
            $row->auto_approval = $autoApproval;
            $row->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return [
            'success' => true,
            'community' => $community,
            'member' => $member,
            'user' => $user,
            'link' => $this->generateInviteLoginUrl($token, $community->url),
        ];
    }

    /**
     * @param string $token
     * @param string $communityUrl
     * @return string
     */
    private function generateInviteLoginUrl(string $token, string $communityUrl): string
    {
        return sprintf(self::USER_INVITE_LOGIN_URL, config('app.url'), $token, $communityUrl);
    }

    /**
     * @param string $token
     * @param string|null $communityUrl
     * @return string
     */
    public function generateResetPasswordUrl(string $token, ?string $communityUrl): string
    {
        return sprintf(self::RESET_PASSWORD_URL, config('app.url'), $token, $communityUrl);
    }

    /**
     * @param string $communityUrl
     * @param string $postUrl
     * @return string
     */
    public function generateViewPostUrl(string $communityUrl, string $postUrl): string
    {
        return sprintf(self::VIEW_POST_URL, config('app.url'), $communityUrl, $postUrl);
    }

    /**
     * @param string $communityUrl
     * @return string
     */
    public function generateJoinRequestUrl(string $communityUrl): string
    {
        return sprintf(self::JOIN_REQUEST_URL, config('app.url'), $communityUrl);
    }

    /**
     * @param string $communityUrl
     * @return string
     */
    private function generateUnsubscribeUrl(string $communityUrl): string
    {
        return sprintf(self::UNSUBSCRIBE_DIGEST_URL, config('app.url'), $communityUrl);
    }

    /**
     * Get Digest User Notification infos for sending emails according to period
     *
     * @param int $communityId
     * @param string $period
     * @return array
     */
    private function getDigestNotificationInfos(int $communityId, string $period): array
    {
        $infos = [];

        $notificationSettings = CommunityMemberSetting::where(['community_id' => $communityId, 'popular_interval' => $period])->get();
        foreach ($notificationSettings as $notificationSetting) {
            $member = CommunityMember::find($notificationSetting->member_id);
            if (!$member) {
                continue;
            }

            $user = User::find($member->user_id);
            if (!$user) {
                continue;
            }

            $infos[] = [
                'user' => $user,
                'member' => $member,
                'already_sents' => DigestPostsSent::getSentPostIds($communityId, $member->id),
            ];
        }

        return $infos;
    }

    /**
     * Get summary post info for sending digest email
     *
     * @param object $post
     * @return array
     */
    private function getSummaryPostForDigest(object $post): array
    {
        $gravatar = 'https://wolfeo.me/assets/img/avatars/default.png';
        if (!empty($post->member->photo)) {
            $gravatar = $post->member->photo;
        } elseif (!empty($post->member->user) && !empty($post->member->user->email)) {
            $gravatar = 'https://www.gravatar.com/avatar/' . md5(strtolower($post->member->user->email)) . '?s=48&d=identicon';
        }

        $name = '';
        if (!empty($post->member)) {
            $name = trim($post->member->user->firstname . ' ' . $post->member->user->lastname);
        }

        if (empty($name) && !empty($post->member->user)) {
            $name = trim($post->member->user->firstname . ' ' . $post->member->user->lastname);
            if (empty($name) && !empty($post->member->user->email)) {
                $name = $post->member->user->email;
            }
        }

        $link = sprintf(self::VIEW_POST_URL, config('app.url'), $post->communityUrl, $post->path);
        $likes = ElementLike::getNumberOfLikeElement($post->id, ElementLike::POST);

        $comments = 0;
        if (!empty($post->comments)) {
            $comments = count($post->comments);
        }

        return [
            'gravatar' => $gravatar,
            'name' => $name,
            'title' => $post->title,
            'content' => html_entity_decode(strip_tags($post->content)),
            'link' => $link,
            'likes' => $likes,
            'comments' => $comments,
        ];
    }

    /**
     * Regenerate receiver posts per community for digest
     *
     * @param array $receiver
     * @return array
     */
    private function regeneratePostsForDigest(array $receiver): array
    {
        $memberId = $receiver['member_id'];
        $communityInfos = $receiver['communityInfos'];

        $posts = [];
        $usedIds = [];
        $usedIdsPerCommunity = [];
        $summaryInfos = [];

        foreach ($communityInfos as $communityId => $communityInfo) {
            if (!array_key_exists($communityId, $summaryInfos)) {
                $summaryInfos[$communityId] = [
                    'communityId' => $communityInfo['communityId'],
                    'communityName' => $communityInfo['communityName'],
                    'communityLogo' => $communityInfo['communityLogo'],
                    'unsubscribeLink' => $communityInfo['unsubscribeLink'],
                    'posts' => [],
                ];
            }

            $postsPerCommunity = $communityInfo['posts'];
            $usedIdsPerCommunity[$communityId] = [];
            $postCnt = 0;
            if (!empty($postsPerCommunity)) {
                foreach ($postsPerCommunity as $postId => $post) {
                    $summaryInfos[$communityId]['posts'][$postId] = $post;
                    $posts[$postId] = $post;
                    $usedIds[] = $postId;
                    $usedIdsPerCommunity[$communityId][] = $postId;

                    $postCnt++;
                    if ($postCnt > 2) {
                        break;
                    }
                }
            }
        }

        if (count($posts) < 10) {
            foreach ($communityInfos as $communityId => $communityInfo) {
                $postsPerCommunity = $communityInfo['posts'];
                if (!empty($postsPerCommunity)) {
                    foreach ($postsPerCommunity as $postId => $post) {
                        if (!in_array($postId, $usedIds)) {
                            $summaryInfos[$communityId]['posts'][$postId] = $post;
                            $posts[$postId] = $post;
                            $usedIds[] = $postId;
                            $usedIdsPerCommunity[$communityId][] = $postId;

                            if (count($posts) >= 10) {
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        foreach ($usedIdsPerCommunity as $communityId => $postIds) {
            $alreadySentIds = DigestPostsSent::getSentPostIds($communityId, $memberId);
            $newPopularIntervalSentIds = array_unique(array_diff($postIds, $alreadySentIds));

            foreach ($newPopularIntervalSentIds as $key => $newPopularIntervalSentId) {
                try {
                    $row = new DigestPostsSent();
                    $row->community_id = $communityId;
                    $row->member_id = $memberId;
                    $row->post_id = $newPopularIntervalSentId;
                    $row->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }
        }

        if (empty($posts)) {
            return ['success' => false, 'message' => __('Post does not exist.')];
        }

        return [
            'success' => true,
            'summaryInfos' => $summaryInfos,
        ];
    }

    /**
     * Send Digest Email notification according to period
     *
     * @param string $period
     * @return array
     */
    public function sendPopularInterval(string $period): array
    {
        $communities = Community::orderBy('created_at', 'asc')->get();
        if (!$communities) {
            return ['success' => false, 'message' => __('Community not found.')];
        }

        $receivers = [];
        $timeLimit = DatetimeHelper::getTimeLimit($period);

        foreach ($communities as $community) {
            $infos = $this->getDigestNotificationInfos($community->id, $period);
            $posts = CommunityPost::getPosts($community->id, $timeLimit);

            foreach ($infos as $info) {
                $user = $info['user'];
                $member = $info['member'];
                $already_sents = $info['already_sents'];

                $postsForSend = [];
                $unsubscribeLink = '';
                if ($posts) {
                    $unsubscribeLink = $this->generateUnsubscribeUrl($community->url);
                    foreach ($posts as $post) {
                        if (!in_array($post->id, $already_sents) && $post->member->id !== $member->id) {
                            $post->communityUrl = $community->url;
                            $postsForSend[$post->id] = $this->getSummaryPostForDigest($post);
                        }
                    }
                }

                $communityInfo = [
                    'communityId' => $community->id,
                    'communityName' => $community->name,
                    'communityLogo' => $community->logo,
                    'unsubscribeLink' => $unsubscribeLink,
                    'posts' => $postsForSend,
                ];

                if (array_key_exists($user->id, $receivers)) {
                    $receivers[$user->id]['communityInfos'][$community->id] = $communityInfo;
                } else {
                    $receivers[$user->id] = [
                        'member_id' => $member->id,
                        'email' => $user->email,
                        'language' => $user->language ?? LangEnum::LANG_ENGLISH,
                        'communityInfos' => [
                            $community->id => $communityInfo,
                        ],
                    ];
                }
            }
        }

        foreach ($receivers as $receiver) {
            $email = $receiver['email'];
            $language = $receiver['language'];

            $result = $this->regeneratePostsForDigest($receiver);
            if ($result['success'] && $result['summaryInfos']) {
                try {
                    Mail::to($email)->send(new PopularIntervalMail(
                        $language,
                        $period,
                        $result['summaryInfos']
                    ));
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }
        }

        return ['success' => true, 'message' => __('Digest emails sent successfully.')];
    }

    /**
     * Get Unread Notification infos for sending emails according to period
     *
     * @param int $communityId
     * @param string $period
     * @return array
     */
    private function getUnreadNotificationInfos(int $communityId, string $period): array
    {
        $infos = [];
        $notificationSettings = CommunityMemberSetting::where(['community_id' => $communityId, 'unread_interval' => $period])->get();

        foreach ($notificationSettings as $notificationSetting) {
            $memberId = $notificationSetting->member_id;
            $member = CommunityMember::find($memberId);
            if (!empty($member)) {
                $user = User::find($member->user_id);
                if (!empty($user->email)) {
                    $infos[] = [
                        'user' => $user,
                        'member' => $member,
                        'already_sent_notification_ids' => UnreadNotificationsSent::getSentNotificationIds($communityId, $memberId, UnreadNotificationsSent::NOTIFICATION_TYPE),
                        'already_sent_chat_ids' => UnreadNotificationsSent::getSentNotificationIds($communityId, $memberId, UnreadNotificationsSent::CHAT_TYPE),
                    ];
                }
            }
        }

        return $infos;
    }

    /**
     * Regenerate receiver infos per community for unreads
     *
     * @param array $receiver
     * @return array
     */
    private function regenerateInfosForUnread(array $receiver): array
    {
        $memberId = $receiver['member_id'];
        $communityInfos = $receiver['communityInfos'];
        $unreadCnt = $receiver['unreadCnt'];

        $unreads = [];
        $summaryInfos = [];
        $usedNotificationIdsPerCommunity = [];
        $usedChatIdsPerCommunity = [];

        foreach ($communityInfos as $communityId => $communityInfo) {
            if (!array_key_exists($communityId, $summaryInfos)) {
                $summaryInfos[$communityId] = [
                    'communityId' => $communityInfo['communityId'],
                    'communityName' => $communityInfo['communityName'],
                    'communityLogo' => $communityInfo['communityLogo'],
                    'manageNotificationLink' => $communityInfo['manageNotificationLink'],
                    'unreads' => [],
                ];
            }

            $usedNotificationIdsPerCommunity[$communityId] = [];
            $usedChatIdsPerCommunity[$communityId] = [];

            $unreadsPerCommunity = $communityInfo['unreads'];
            $itemCnt = 0;

            if (!empty($unreadsPerCommunity)) {
                foreach ($unreadsPerCommunity as $item) {
                    $summaryInfos[$communityId]['unreads'][] = $item;
                    $unreads[] = $item;

                    if ($item->type === UnreadNotificationsSent::NOTIFICATION_TYPE) {
                        $usedNotificationIdsPerCommunity[$communityId][] = $item->id;
                    } else if ($item->type === UnreadNotificationsSent::CHAT_TYPE) {
                        $usedChatIdsPerCommunity[$communityId][] = $item->id;
                    }

                    $itemCnt++;
                    if ($itemCnt > 2) {
                        break;
                    }
                }
            }
        }

        foreach ($usedNotificationIdsPerCommunity as $communityId => $notificationIds) {
            $alreadySentIds = UnreadNotificationsSent::getSentNotificationIds($communityId, $memberId, UnreadNotificationsSent::NOTIFICATION_TYPE);
            $newUnreadSentIds = array_unique(array_diff($notificationIds, $alreadySentIds));

            foreach ($newUnreadSentIds as $key => $newUnreadSentId) {
                try {
                    $row = new UnreadNotificationsSent();
                    $row->community_id = $communityId;
                    $row->member_id = $memberId;
                    $row->type = UnreadNotificationsSent::NOTIFICATION_TYPE;
                    $row->notification_id = $newUnreadSentId;
                    $row->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }
        }

        foreach ($usedChatIdsPerCommunity as $communityId => $notificationIds) {
            $alreadySentIds = UnreadNotificationsSent::getSentNotificationIds($communityId, $memberId, UnreadNotificationsSent::CHAT_TYPE);
            $newUnreadSentIds = array_unique(array_diff($notificationIds, $alreadySentIds));

            foreach ($newUnreadSentIds as $key => $newUnreadSentId) {
                try {
                    $row = new UnreadNotificationsSent();
                    $row->community_id = $communityId;
                    $row->member_id = $memberId;
                    $row->type = UnreadNotificationsSent::CHAT_TYPE;
                    $row->notification_id = $newUnreadSentId;
                    $row->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }
        }

        if (empty($unreads)) {
            return ['success' => false, 'message' => __('Unread does not exist.')];
        }

        $summaryInfos['unreadCnt'] = $unreadCnt;

        return [
            'success' => true,
            'summaryInfos' => $summaryInfos,
        ];
    }

    /**
     * Send Unread Notification Email according to period
     *
     * @param string $period
     * @return array
     */
    public function sendUnreadInterval(string $period): array
    {
        $communities = Community::orderBy('created_at', 'asc')->get();
        if (!$communities) {
            return ['success' => false, 'message' => __('Community not found.')];
        }

        $receivers = [];
        $timeLimit = DatetimeHelper::getTimeLimit($period);

        foreach ($communities as $community) {
            $unreadNotifications = Notification::getUnreadNotitications($community->id, $timeLimit);
            $infos = $this->getUnreadNotificationInfos($community->id, $period);

            foreach ($infos as $info) {
                $user = $info['user'];
                $member = $info['member'];
                $alreadySentNotificationIds = $info['already_sent_notification_ids'];
                $alreadySentChatIds = $info['already_sent_chat_ids'];

                $unreads = [];
                $unreadCnt = 0;
                $manageNotificationLink = '';

                if (!empty($unreadNotifications)) {
                    $manageNotificationLink = $this->generateUnsubscribeUrl($community->url);
                    foreach ($unreadNotifications as $item) {
                        $ownerMemberSetting = CommunityMemberSetting::where([
                            'community_id' => $community->id,
                            'member_id' => $item->owner_id
                        ])->first();

                        if (!empty($ownerMemberSetting) && $ownerMemberSetting->reply === CommunityMemberSetting::OPT_NOTIFY_YES && $item->owner_id === $member->id) {
                            $unreadCnt++;
                            if (!in_array($item->id, $alreadySentNotificationIds)) {
                                $notification = Notification::getNotificationInfo($item);
                                $redirectUrl = Notification::getRedirectUrl($item->id);

                                if (!empty($redirectUrl)) {
                                    $notification->redirectUrl = $this->generateViewPostUrl($community->url, $redirectUrl);
                                } else {
                                    $notification->redirectUrl = $this->generateJoinRequestUrl($community->url);
                                }

                                $notification->type = UnreadNotificationsSent::NOTIFICATION_TYPE;
                                $unreads[] = $notification;
                            }
                        }
                    }
                }

                $unreadChatUserIds = Chat::getUnreadChatUserIds($user->id, $community->id, $timeLimit);
                $unreadChatUsers = User::whereIn('id', $unreadChatUserIds)
                    ->with('member')
                    ->get();

                if (!empty($unreadChatUsers)) {
                    foreach ($unreadChatUsers as $unreadChatUser) {
                        $lastChat = Chat::getLastChat($unreadChatUser, $user->id);
                        if (!empty($lastChat)) {
                            $unreadCnt++;
                            if (!in_array($lastChat->id, $alreadySentChatIds)) {
                                $lastChat->redirectUrl = sprintf(self::CHAT_URL, config('app.url'), $lastChat->id);
                                $lastChat->type = UnreadNotificationsSent::CHAT_TYPE;
                                $unreads[] = $lastChat;
                            }
                        }
                    }
                }

                // sort unreads by created at with descending order
                usort($unreads, fn($a, $b) => strcmp($b->created_at, $a->created_at));

                $communityInfo = [
                    'communityId' => $community->id,
                    'communityName' => $community->name,
                    'communityLogo' => $community->logo,
                    'manageNotificationLink' => $manageNotificationLink,
                    'unreads' => $unreads,
                ];

                $userEmail = trim($user->email);
                if (array_key_exists($userEmail, $receivers)) {
                    $receivers[$userEmail]['unreadCnt'] += $unreadCnt;
                    $receivers[$userEmail]['communityInfos'][$community->id] = $communityInfo;
                } else {
                    $receivers[$userEmail] = [
                        'member_id' => $member->id,
                        'email' => $userEmail,
                        'language' => $user->language ?? LangEnum::LANG_ENGLISH,
                        'unreadCnt' => $unreadCnt,
                        'communityInfos' => [
                            $community->id => $communityInfo,
                        ],
                    ];
                }
            }
        }
        
        $emailSent = [];
        foreach ($receivers as $receiver) {
            $email = trim($receiver['email']);
            $language = $receiver['language'];

            if (in_array($email, $emailSent)) {
                continue;
            }

            $result = $this->regenerateInfosForUnread($receiver);

            if ($result['success'] && $result['summaryInfos']) {
                $emailSent[] = $email;
                try {
                    Mail::to($email)->send(new UnreadIntervalMail(
                        $language,
                        $result['summaryInfos']['unreadCnt'],
                        $result['summaryInfos']
                    ));
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }
        }

        return [
            'success' => true,
            'message' => __('Unread Interval emails sent successfully.'),
        ];
    }

    /**
     * Get latest community url
     *
     * @return string|null
     */
    public function getLatestUrl(): ?string
    {
        $community = null;

        $user = User::where(['id' => session('user_id')])->first();
        if ($user) {
            $community = Community::where(['user_id' => session('user_id')])->first();
            if (!$community) {
                $communityHasMember = CommunityMember::where(['user_id' => $user->id, 'access' => 1])->first();
                if (!$communityHasMember) {
                    $communityHasMember = CommunityMember::where(['user_id' => $user->id])->first();
                }
                if ($communityHasMember) {
                    $community = Community::where(['id' => $communityHasMember->community_id])->first();
                }
            }
        }

        if (!$community) {
            $community = Community::getIncubateurCommunity();
        }

        return $community->url;
    }

    /**
     * Validate community name and url
     *
     * @param string $url
     * @return array
     */
    public function validateCommunityData(string $url): array
    {
        if (in_array($url, self::PREDEFINED_URLS)) {
            return ['success' => false, 'message' => __('Community url is not valid, please try another url.')];
        }

        return ['success' => true];
    }

    /**
     * Send reminder email for trial plan end
     *
     * @param int $days
     * @return bool
     */
    public function sendReminderEmailForTrialPlan(int $days): bool
    {
        foreach (CommunityPlan::getNextBillingTrialPlans($days) as $plan)
        {
            try {
                Mail::to($plan->community->user->email)->send(new CommunityTrialPlanReminderMail($plan));
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }

        return true;
    }

    /**
     * Migrate community media data
     *
     * @param MediaService $mediaService
     * @return array
     */
    public function migrateCommunityMediaData(MediaService $mediaService): array
    {
        $communities = Community::orderBy('created_at', 'asc')->get();
        if (!$communities) {
            return ['success' => false, 'message' => __('Community not found.')];
        }

        foreach ($communities as $community) {
            $this->generateCommunityMedias($community, $mediaService);
        }

        $medias = CommunityMedia::orderBy('created_at', 'asc')->get();
        if (!empty($medias)) {
            foreach ($medias as $media) {
                $mediaArray = $media->toArray();
                $mediaService->createMedia($mediaArray, $mediaArray['owner'], $mediaArray['owner_id']);
            }
        }

        return ['success' => true];
    }

    /**
     * Generate community media data
     *
     * @param Community $community
     * @param MediaService $mediaService
     * @return void
     */
    private function generateCommunityMedias(Community $community, MediaService $mediaService): void
    {
        if (!empty($community->video)) {
            $mediaService->createNewMedia($community->video, Medias::TYPE_VIDEO, Medias::OWNER_COMMUNITY, $community->id);
        }

        if (!empty($community->photo)) {
            $mediaService->createNewMedia($community->photo, Medias::TYPE_IMAGE, Medias::OWNER_COMMUNITY, $community->id);
        }
    }

    /**
     * @param string $name
     * @param string $url
     * @param int $userId
     * @return array
     */
    public function createCommunity(
        string $name,
        string $url,
        int $userId
    ): array {
        try {
            $community = Community::firstOrNew([
                'user_id' => $userId,
                'name' => $name,
                'privacy' => Community::PRIVACY_PRIVATE,
                'url' => $url,
                'status' => Community::STATUS_PUBLISHED,
            ]);

            $community->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        try {
            $this->levelService->generateLevels($community->id);
            $this->extensionService->generateExtensions($community->id);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }

        return [
            'success' => true,
            'community' => $community,
        ];
    }

    /**
     * Update the number of free trial days for a specific community
     *
     * @param Community $community
     * @param int $trialDays
     * @return array
     */
    public function updateFreeTrialDays(Community $community, int $trialDays): array
    {
        try {
            $community->trial_days = $trialDays;
            $community->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->fail(__('Failed to update free trial days.'));
        }

        return $this->success(['message' => __('Free trial days updated.')]);
    }

    /**
     * @return void
     */
    public function shortenInviteToken(): void
    {
        $inviteTokens = InviteUserTokens::all();
        if (!$inviteTokens) {
            return;
        }

        foreach ($inviteTokens as $inviteToken) {
            try {
                $inviteToken->token = InviteUserTokens::generateUniqToken();
                $inviteToken->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }
    }

    /**
     * @param int $communityId
     * @return array
     */
    public function getCommunityMembersStats(int $communityId): array
    {
        $numberOfMembers = CommunityMember::where(['community_id' => $communityId])
            ->where('access', '<>', CommunityMember::ACCESS_DECLINE)
            ->count();

        $numberOfPendingMembers = CommunityMember::where(['community_id' => $communityId])
            ->where('access', CommunityMember::ACCESS_PENDING)
            ->count();

        $numberOfPosts = CommunityPost::where(['community_id' => $communityId])
            ->count();

        return [$numberOfMembers, $numberOfPendingMembers, $numberOfPosts];
    }

    /**
     * @param string $name
     * @return string
     */
    public function generateUniqUrl(string $name): string
    {
        $valid = false;
        $url = trim(strtolower(str_replace(' ', '', $name)));

        $count = Community::where('url', '=', $url)->count();
        if ($count === 0 && !in_array($url, self::PREDEFINED_URLS)) {
            return $url;
        }

        while ($valid === false) {
            $url = strtolower(str_replace(' ', '', $name)) . mt_rand(1000, 9999);
            $count = Community::where('url', '=', $url)->count();
            if ($count === 0) {
                $valid = true;
                break;
            }
        }

        return $url;
    }

    /**
     * Clean community table
     *
     * @return void
     */
    public function cleanCommunityTable(): void
    {
        $existingData = [];
        $communities = Community::all();
        foreach ($communities as $community) {
            if (!array_key_exists($community->url, $existingData)) {
                $existingData[$community->url] = $community->name;
            } else if ($existingData[$community->url] === $community->name) {
                try {
                    $this->levelService->removeLevels($community->id);
                    $this->extensionService->removeExtensions($community->id);
                    $community->delete();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                }
            }
        }
    }
}
