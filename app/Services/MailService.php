<?php

namespace App\Services;

use App\Enum\LangEnum;
use App\Mail\Authenticator\TwoFactorAuthenticationCodeMail;
use App\Mail\CommunityClosedMail;
use App\Mail\CommunityCreatedMail;
use App\Mail\CommunityPlanCancelMail;
use App\Mail\CommunityPlanOverdue;
use App\Mail\CommunityPlanRenewalReminderMail;
use App\Mail\CommunityReactivatedMail;
use App\Mail\CommunityTrialPlanReminderMail;
use App\Mail\Notifications\JoinRequestNotificationMail;
use App\Mail\Notifications\NewPostNotificationMail;
use App\Mail\ResetPasswordMail;
use App\Mail\Subscriptions\SubscriptionCancelled;
use App\Mail\Subscriptions\SubscriptionOverdue;
use App\Mail\Subscriptions\SubscriptionRenewalReminderMail;
use App\Mail\UserInviteMail;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityMemberSetting;
use App\Models\CommunityPlan;
use App\Models\CommunityPost;
use App\Models\Members\Subscriptions\MemberSubscriptions;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class MailService
{
    private CommunityService $communityService;

    public function __construct(CommunityService $communityService)
    {
        $this->communityService = $communityService;
    }

    /**
     * @param int $communityId
     * @param int $memberId
     * @param string $email
     * @return array
     */
    public function sendInviteEmail(int $communityId, int $memberId, string $email): array
    {
        $response = $this->communityService->getInviteShareLink($communityId, $memberId, true);
        if (!$response['success'] || empty($response['link'])) {
            return $response;
        }

        $community = $response['community'];
        $member = $response['member'];
        $user = $response['user'];
        $inviteLink = $response['link'];

        $language = $user->language ?? LangEnum::LANG_ENGLISH;

        try {
            Mail::to($email)->send(new UserInviteMail(
                $member,
                $community->name,
                $inviteLink,
                $language
            ));
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return [
            'success' => true,
            'message' => __('Invitation sent successfully.'),
        ];
    }

    /**
     * @param string $email
     * @param string|null $url
     * @return array
     */
    public function sendResetPasswordEmail(string $email, ?string $url): array
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return ['success' => false, 'message' => __('User email is not found')];
        }

        if ($url) {
            $community = Community::where('url', $url)->first();
            if (!$community) {
                return ['success' => false, 'message' => __('Community not found')];
            }
        }

        $language = $user->language ?? LangEnum::LANG_ENGLISH;
        $token = User::generateUniqToken();

        try {
            $user->token = $token;
            $user->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        $link = $this->communityService->generateResetPasswordUrl($token, $url);

        try {
            Mail::to($email)->send(new ResetPasswordMail(
                $user,
                $link
            ));
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return [
            'success' => true,
            'message' => __('Email sent successfully.'),
        ];
    }

    /**
     * @param Community $community
     * @param CommunityPost $communityPost
     * @param CommunityMember $member
     * @param array $receiverUserIds
     * @return array
     */
    private function sendEmailForNewPost(
        Community $community,
        CommunityPost $communityPost,
        CommunityMember $member,
        array $receiverUserIds
    ): array
    {
        if (!$communityPost->path) {
            return [
                'success' => false,
                'message' => __('Post url does not exist.')
            ];
        }

        $communityOwner = $community->user;

        $replyTo = $communityOwner->email;
        if ($communityOwner->id === 1) {
            $replyTo = config('mail.support');
        }

        $users = User::select('email','language')->whereIn('id', $receiverUserIds)->get();
        foreach ($users as $user) {
            $language = $user->language ?? LangEnum::LANG_ENGLISH;
            try {
                Mail::to($user->email)->send(new NewPostNotificationMail(
                    $member,
                    $communityPost,
                    $this->communityService->generateViewPostUrl($community->url, $communityPost->path),
                    $replyTo,
                    $language
                ));
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return [
            'success' => true,
            'message' => __('Email sent successfully.')
        ];
    }

    /**
     * @param int $postId
     * @param int $memberId
     * @return array
     */
    public function sendEmailNotificationForNewPost(int $postId, int $memberId): array
    {
        $communityPost = CommunityPost::find($postId);
        if (!$communityPost) {
            return ['success' => false, 'message' => __('Community post not found.')];
        }

        $creator = CommunityMember::find($memberId);
        if (!$creator) {
            return ['success' => false, 'message' => __('Community member not found.')];
        }

        $receiverMemberIds = CommunityMemberSetting::where(['community_id' => $communityPost->community_id, 'admin_announce' => 1])
            ->pluck('member_id')
            ->toArray();

        if (!$receiverMemberIds) {
            return ['success' => false, 'message' => __('No members found for this notification.')];
        }

        $receiverMemberIds = array_unique($receiverMemberIds);
        $receiverUserIds = CommunityMember::whereIn('id', $receiverMemberIds)
            ->pluck('user_id')
            ->toArray();

        $receiverUserIds = array_unique($receiverUserIds);

        $this->sendEmailForNewPost(
            $communityPost->community,
            $communityPost,
            $creator,
            $receiverUserIds
        );

        return ['success' => true, 'message' => __('Email sent successfully.')];
    }

    /**
     * @param Community $community
     * @param CommunityMember $receiver
     * @param int $access
     * @param string $userName
     * @return array
     */
    private function sendEmailForJoinRequest(Community $community, CommunityMember $receiver, int $access, string $userName): array
    {
        $user = User::find($receiver->user_id);
        if (!$user) {
            return ['success' => false, 'message' => __('User not found.')];
        }

        $language = $user->language ?? LangEnum::LANG_ENGLISH;

        try {
            Mail::to($user->email)->send(new JoinRequestNotificationMail(
                $community,
                $this->communityService->generateJoinRequestUrl($community->url),
                $access,
                $userName,
                $language
            ));
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true, 'message' => __('Email sent successfully.')];
    }

    /**
     * @param int $communityId
     * @param int $memberId
     * @param int $access
     * @param string $feedback
     * @return array
     */
    public function sendEmailNotificationForJoinRequest(int $communityId, int $memberId, int $access, string $userName): array
    {
        $community = Community::find($communityId);
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found.')];
        }

        $receiver = CommunityMember::find($memberId);
        if (!$receiver) {
            return ['success' => false, 'message' => __('Community member not found.')];
        }

        try {
            $this->sendEmailForJoinRequest(
                $community,
                $receiver,
                $access,
                $userName
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true, 'message' => __('Email sent successfully.')];
    }

    public function displayEmail(
        string $view,
        object $data,
        string $language = 'en'
    )
    {
        return view("emails.". $language ."." . $view, [
            'memberName' => $data->user?->name,
            'communityName' => $data->community?->name,
            'communityLogo' => $data->community?->logo,
            'communityUrl' => $data->community?->url,
            'card' => [
                'brand' => $data->payment_method?->card_brand,
                'last4' => $data->payment_method?->last4,
            ],
            'subscription' => [
                'amount' => $data->amount,
                'currency' => '€',
                'period' => $data->period,
            ],
            'nextBillingDate' => date("F jS, Y", strtotime($data->next_billing_at)),
            'twoFactorCode' => sprintf("%06d", mt_rand(1, 999999)),
        ]);
    }

    public function testEmails()
    {
        $email = 'contact@studio27.fr';
        $language = 'fr';

        $user = User::find(1);
        $community = Community::find(1);
        $member = CommunityMember::find(36);
        $memberSubscription = $subscription = MemberSubscriptions::find(2);
        $communityPlan = CommunityPlan::find(1);
        $communityPost = CommunityPost::find(4);
        $plan = CommunityPlan::find(1);
        $link = 'https://google.fr';

        /*
        Mail::to($email)->send(new ResetPasswordMail($user, $link));
        Mail::to($email)->send(new TwoFactorAuthenticationCodeMail($user, '12345'));

        Mail::to($email)->send(new CommunityCreatedMail($community));
        Mail::to($email)->send(new CommunityPlanOverdue($plan));
        Mail::to($email)->send(new CommunityPlanRenewalReminderMail($communityPlan));
        Mail::to($email)->send(new CommunityClosedMail($plan));
        Mail::to($email)->send(new CommunityReactivatedMail($community));
        Mail::to($email)->send(new CommunityPlanCancelMail($plan));
        Mail::to($email)->send(new CommunityTrialPlanReminderMail($plan));

        Mail::to($email)->send(new SubscriptionCancelled($subscription));
        Mail::to($email)->send(new SubscriptionOverdue($subscription));
        Mail::to($email)->send(new SubscriptionRenewalReminderMail($subscription));
        */



        /*
        // JoinRequestNotificationMail
        Mail::to($email)->send(new JoinRequestNotificationMail(
            $community,
            $this->communityService->generateJoinRequestUrl($community->url),
            1,
            'Test',
            $language
        ));
        Mail::to($email)->send(new JoinRequestNotificationMail(
            $community,
            $this->communityService->generateJoinRequestUrl($community->url),
            -1,
            'Test',
            $language
        ));
        Mail::to($email)->send(new JoinRequestNotificationMail(
            $community,
            $this->communityService->generateJoinRequestUrl($community->url),
            -2,
            'Test',
            $language
        ));

        // NewPostNotificationMail
        Mail::to($email)->send(new NewPostNotificationMail(
            $member,
            $communityPost,
            $this->communityService->generateViewPostUrl($community->url, $communityPost->path),
            $language
        ));

        $price = CurrencyHelper::format($memberSubscription->amount);
        if ($memberSubscription->period === MemberSubscriptions::PERIOD_YEARLY) {
            $price .= '/' . __('year', [], $user->language);
        } else {
            $price .= '/' . __('month', [], $user->language);
        }

        // NewSubscriptionStartedMail
        Mail::to($email)->send(new NewSubscriptionStartedMail(
            community: $community,
            user: $user,
            price: $price
        ));


        /*
        $event = CommunityEvent::find(1);
        Mail::to($email)->send(new EventReminderMail(
            $event->title,
            $event->start_at,
            $event->timezone,
            $language,
            $event->description,
            $event->link
        ));
        */

        // PopularIntervalMail
        // @todo

        // UnreadIntervalMail
        // @todo

        // UserInviteMail
        /*
        Mail::to($email)->send(new UserInviteMail(
            $member,
            $community->name,
            $link,
            $language
        ));
        */
    }
}
