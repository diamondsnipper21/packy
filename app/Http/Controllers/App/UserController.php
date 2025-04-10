<?php

namespace App\Http\Controllers\App;

use App\Enum\LangEnum;
use App\Helpers\TextHelper;
use App\Models\Chat;
use App\Models\Community;
use App\Models\User;
use App\Models\Visit;
use App\Services\CommunityService;
use App\Services\LoggerService;
use App\Services\MemberService;
use App\Services\StripeService;
use App\Services\UserService;
use App\Services\VisitService;
use Illuminate\Http\Request;

class UserController extends AppController
{
    /**
     * @param Request $request
     * @param UserService $userService
     * @return array
     */
    public function get(Request $request, UserService $userService): array
    {
        return [
            'success' => true,
            'paginatedUsers' => $userService->getPaginatedUsers($request->search, $request->page ?? 0),
            'communities' => Community::select('id', 'name')->get()
        ];
    }

    /**
     * Get one user
     *
     * @param int $userId
     * @return array
     */
    public function getOne(int $userId): array
    {
        $user = User::where(['id' => $userId])
            ->with('member')
            ->with('member.user')
            ->first();

        if (!$user) {
            return ['success' => false, 'message' => __('User not found')];
        }

        $unreadChatsCnt = 0;

        if (!empty($user)) {
            $unreadChatsCnt = count(Chat::getUnreadChatUserIds($user->id));
        }

        return [
            'success' => true,
            'user' => $user,
            'unreadChatsCnt' => $unreadChatsCnt
        ];
    }

    /**
     * Log in as another user
     *
     * @param int $userId
     * @param CommunityService $communityService
     * @return array
     */
    public function loginAsUser(int $userId, CommunityService $communityService): array
    {
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'message' => __('User not found')];
        }

        auth()->login($user);
        session()->put('user_id', $user->id);

        return [
            'success' => true,
            'user' => User::getUserInfo(),
            'validUrl' => $communityService->getLatestUrl()
        ];
    }

    /**
     * @param Request $request
     * @param VisitService $visitService
     * @return array
     */
    public function registerVisitor(Request $request, VisitService $visitService): array
    {
        $communityId = $request->communityId ?? 0;

        $community = Community::where(['id' => $communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        $page = $request->page ?? '';
        if (!in_array($page, Visit::TRACKED_PAGES)) {
            return ['success' => false, 'message' => __('Invalid Page')];
        }

        return $visitService->save($communityId, $page);
    }

    /**
     * @param Request $request
     * @param MemberService $memberService
     * @param CommunityService $communityService
     * @return array
     */
    public function completeProfile(Request $request, MemberService $memberService, CommunityService $communityService): array
    {
        $id = $request->id ?? 0;
        $tag = $request->tag ?? null;
        $bio = $request->bio ?? null;
        $photo = $request->photo ?? null;
        $link = $request->link ?? null;
        $country = $request->country ?? null;

        $user = User::find($id);
        if (!$user) {
            return ['success' => false, 'message' => __('User not found')];
        }

        if ($tag) {
            $existingTag = User::where(['tag' => $tag])->where('id', '!=', $user->id)->first();
            if ($existingTag) {
                return ['success' => false, 'message' => __('The provided tag already exists.')];
            }
        }

        if (empty($tag)) {
            $tag = $memberService->generateUniqTag($user->name);
        }

        try {
            $user->tag = $tag;
            $user->bio = $bio;
            $user->photo = $photo;
            $user->link = $link;
            $user->last_activity = date('Y-m-d H:i:s');
            $user->timezone = $request->timezone;
            $user->country = $country;
            $user->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }

        return [
            'success' => true,
            'user' => User::where(['id' => $user->id])->with('member')->first(),
            'validUrl' => $communityService->getLatestUrl(),
        ];
    }

    /**
     * @param Request $request
     * @param MemberService $memberService
     * @return array
     */
    public function updateProfile(Request $request, MemberService $memberService): array
    {
        $communityId = $request->communityId ?? 0;
        $id = $request->id ?? 0;
        $email = TextHelper::removeSpecialChars($request->email);
        $tag = $request->tag ?? null;
        $content = $request->bio ?? null;
        $photo = $request->photo ?? null;
        $link = $request->link ?? null;
        $country = $request->country ?? null;
        $language = $request->language ?? LangEnum::LANG_ENGLISH;
        $timezone = $request->timezone ?? null;

        $user = User::find($id);
        if (!$user) {
            return ['success' => false, 'message' => __('User not found')];
        }

        if ($tag) {
            $existingTag = User::where(['tag' => $tag])->whereNotIn('id', [$id])->first();
            if ($existingTag) {
                return ['success' => false, 'message' => __('The provided tag already exists.')];
            }
        }

        if (!$tag) {
            $tag = strtolower(str_replace(' ', '_', $user->name));
        }

        $stripeCustomerUpdate = [];
        if ($user->email !== $email) {
            $stripeCustomerUpdate['email'] = $email;
        }
        if ($user->firstname !== $request->firstname || $user->lastname !== $request->lastname) {
            $stripeCustomerUpdate['name'] = trim($request->firstname . ' ' . $request->lastname);
        }

        try {
            $user->email = $email;
            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->language = $language;
            $user->country = $country;
            $user->tag = $tag;
            $user->bio = $content;
            $user->photo = $photo;
            $user->link = $link;
            $user->timezone = $timezone;
            $user->last_activity = date('Y-m-d H:i:s');
            $user->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        // if user email has changed -> we update customer email on Stripe
        if ($stripeCustomerUpdate) {
            $this->updateStripeCustomersWithNewProfileData($user, $stripeCustomerUpdate);
        }

        return [
            'success' => true,
            'message' => __('Profile updated.'),
            'member' => $memberService->getMember($communityId, $user->id),
        ];
    }

    /**
     * @param User $user
     * @param array $data
     * @return void
     */
    private function updateStripeCustomersWithNewProfileData(User $user, array $data): void
    {
        try {
            if ($user->stripe_customer_id) {
                $stripeServiceSubscriptions = new StripeService(config('payment.stripe.subscriptions_secret_key'));
                $stripeServiceSubscriptions->updateCustomer($user->stripe_customer_id, $data);
            }
            if ($user->stripe_customer_id_marketplace) {
                $stripeServiceMarketplace = new StripeService();
                $stripeServiceMarketplace->updateCustomer($user->stripe_customer_id, $data);
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }
}
