<?php

namespace App\Http\Controllers\App;

use App\Enum\LangEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\TextHelper;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityPlan;
use App\Models\Medias;
use App\Models\User;
use App\Models\UserPaymentMethod;
use App\Services\LoggerService;
use App\Services\MemberService;
use App\Services\PayoutService;
use App\Services\StripeService;
use App\Services\CommunityService;
use App\Services\MediaService;
use App\Mail\CommunityCreatedMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CommunityController extends AppController
{
    /**
     * Create new community
     *
     * @param Request $request
     * @param CommunityService $communityService
     * @return JsonResponse
     */
    public function store(
        Request $request,
        CommunityService $communityService
    ): JsonResponse {
        $validator = \Validator($request->all(), [
            'name' => 'required',
            'type' => 'required',
            'paymentMethod' => 'required',
        ], [
            'name.required' => __('Name is required.'),
            'type.required' => __('Type is required.'),
            'paymentMethod.required' => __('Payment method is required.'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $name = $request->name;
        $url = $request->url;
        if (!$url) {
            $url = $communityService->generateUniqUrl($name);
        }
        $url = TextHelper::preventSpecialChars($url);
        if (in_array($url, CommunityService::PREDEFINED_URLS)) {
            return response()->json([
                'success' => false,
                'message' => __('Community url is not valid, please try another url.'),
            ], 400);
        }

        $user = User::where(['id' => session('user_id')])->first();


        $stripeService = new StripeService(config('payment.stripe.subscriptions_secret_key'));

        // create customer if necessary
        if (!$user->stripe_customer_id) {
            $customer = $stripeService->createCustomer(
                email: $user->email,
                name: $user->name,
                country: $user->country
            );

            try {
                $user->stripe_customer_id = $customer->id;
                $user->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }
        }

        // get/create payment method
        if ($request->type === 'stripe_payment_method') {
            try {
                $paymentMethod = $stripeService->attachUserPaymentMethod(
                    user: $user,
                    data: $request->paymentMethod
                );
            } catch (\Stripe\Exception\ApiErrorException $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getError());
                return response()->json([
                    'success' => false,
                    'message' => __('stripe-errors.' . $e->getError()->code),
                ], 400);
            }
        } else {
            $paymentMethod = UserPaymentMethod::where(['id' => (int)$request->paymentMethod])->first();
        }

        if (!$paymentMethod) {
            return $this->error(404, [
                'message' => __('Payment method not found.'),
            ]);
        }

        try {
            $createSubscriptionPlan = $stripeService->createSubscriptionPlan(
                $user->id,
                $paymentMethod,
                true
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        // check for 3ds status
        $setupIntentClientSecret = null;
        if (isset($createSubscriptionPlan['pending_setup_intent']) && $createSubscriptionPlan['pending_setup_intent']) {
            $setupIntent = $stripeService->retrieveSetupIntent($createSubscriptionPlan['pending_setup_intent']);
            if ($setupIntent && isset($setupIntent['client_secret'])) {
                $setupIntentClientSecret = $setupIntent['client_secret'];
            }
        }

        session()->put('community_data', [
            'name' => $name,
            'subscription' => $createSubscriptionPlan['subscription'],
            'payment_method' => $paymentMethod,
        ]);

        return response()->json([
            'success' => true,
            'pending_setup_intent' => $setupIntentClientSecret,
        ]);
    }

    /**
     * @param Request $request
     * @param CommunityService $communityService
     * @param PayoutService $payoutService
     * @return JsonResponse
     */
    public function view(
        Request $request,
        CommunityService $communityService,
        PayoutService $payoutService
    ): JsonResponse {
        $auth = false;
        if (auth()->check() === true) {
            $auth = true;
        }

        $communityUrl = $request->communityUrl ?? '';
        $query = Community::query()
            ->where(['url' => $communityUrl])
            ->with('links')
            ->with('categories')
            ->with('medias')
            ->with('price');

        if ($auth) {
            $query->with('rules')
                ->with('groups')
                ->with('products')
                ->with('products.prices')
                ->with('products.prices.subscriptions')
                ->with('extensions')
                ->with('user')
                ->with('notifications')
                ->with('extensions')
                ->with('invoices');
        }

        $community = $query->first();
        if (!$community) {
            return response()->json([
                'success' => false,
                'message' => __('Community not found'),
            ], 404);
        }

        $numberOfMembers = 0;
        $numberOfPendingMembers = 0;
        $numberOfPosts = 0;

        if ($auth) {
            [
                $numberOfMembers,
                $numberOfPendingMembers,
                $numberOfPosts,
            ] = $communityService->getCommunityMembersStats($community->id);
            if (session()->has('2fa_code')) {
                $community->verificationCode = session('2fa_code');
            }
        }
        [$payouts, $transactions] = $payoutService->getPayoutsDataByYear($community->id);

        $community->number_of_members = $numberOfMembers;
        $community->number_of_pending_members = $numberOfPendingMembers;
        $community->number_of_posts = $numberOfPosts;
        $community->payouts = $payouts;
        $community->transactions = $transactions;

        return response()->json([
            'success' => true,
            'data' => $community,
            'app_name' => env('APP_NAME'),
        ]);
    }

    /**
     * Update community props
     *
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     */
    public function update(
        Request $request,
        MediaService $mediaService,
    ): JsonResponse {
        $community = $request->community;
        $name = $request->name ?? '';
        $privacy = $request->privacy ?? Community::PRIVACY_PUBLIC;
        $owner_show = $request->owner_show ?? Community::OWNER_SHOW;
        $summary_description = $request->summary_description ?? '';
        $description = $request->description ?? '';
        $summary_photo = $request->summary_photo ?? '';
        $logo = $request->logo ?? '';
        $medias = $request->medias ?? [];
        $photo = $request->photo ?? '';
        $video = $request->video ?? '';
        $auto_post_approbation = $request->auto_post_approbation ?? Community::AUTO_POST_APPROBATION;

        $url = $request->url ?? strtolower(str_replace(' ', '-', $name));
        $url = TextHelper::preventSpecialChars($url);

        $existingCommunity = Community::where(['url' => $url])->first();
        if ($existingCommunity && $existingCommunity->id !== $community->id) {
            return response()->json([
                'success' => false,
                'message' => __('Community already exists.'),
            ], 400);
        }
        if (in_array($url, CommunityService::PREDEFINED_URLS)) {
            return response()->json([
                'success' => false,
                'message' => __('Community url is not valid, please try another url.'),
            ], 400);
        }

        try {
            $community->name = $name;
            $community->privacy = $privacy;
            $community->owner_show = $owner_show;
            $community->summary_description = $summary_description;
            $community->description = $description;
            $community->summary_photo = $summary_photo;
            $community->logo = $logo;
            $community->url = $url;
            $community->auto_post_approbation = $auto_post_approbation;
            $community->save();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        if (!empty($medias)) {
            $order = 0;
            foreach ($medias as $media) {
                $mediaId = $media['id'] ?? 0;
                $mediaType = $media['type'] ?? '';
                $mediaPath = $media['path'] ?? '';

                if (!$mediaId && !empty($mediaType) && !empty($mediaPath)) {
                    $order++;
                    $mediaService->createNewMedia($mediaPath, $mediaType, Medias::OWNER_COMMUNITY, $community->id,
                        $order);
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => Community::getCommunity($community->id),
            'message' => __('Community has been updated.'),
        ]);
    }

    /**
     * Reactivate Community
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reactivate(Request $request): JsonResponse
    {
        $community = $request->community;
        $member = $request->member;
        $user = $member->user;
        $trial = false;

        // there is not existing plan yet -> we apply trial period
        $existingPlan = CommunityPlan::where(['community_id' => $community->id])->first();
        if (!$existingPlan) {
            $trial = true;
        }

        $stripeService = new StripeService(config('payment.stripe.subscriptions_secret_key'));

        // get/create payment method
        if ($request->type === 'stripe_payment_method') {

            if (!$user->stripe_customer_id) {
                $customer = $stripeService->createCustomer(
                    email: $user->email,
                    name: $user->name,
                    country: $user->country
                );

                try {
                    $user->stripe_customer_id = $customer->id;
                    $user->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getError());
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage(),
                    ], 400);
                }
            }

            try {
                $paymentMethod = $stripeService->attachUserPaymentMethod(
                    user: $user,
                    data: $request->paymentMethod
                );
            } catch (\Stripe\Exception\ApiErrorException $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getError());
                return response()->json([
                    'success' => false,
                    'message' => __('stripe-errors.' . $e->getError()->code),
                ], 400);
            }
        } else {
            $paymentMethod = UserPaymentMethod::where(['id' => (int)$request->paymentMethod])->first();
        }

        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => 'Error',
            ], 400);
        }

        try {
            $createSubscriptionPlan = $stripeService->createSubscriptionPlan(
                $member->user_id,
                $paymentMethod,
                $trial,
                $community
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        session()->put('community_data', [
            'id' => $community->id,
            'subscription' => $createSubscriptionPlan['subscription'],
            'payment_method' => $paymentMethod,
        ]);

        sleep(1);

        $paymentIntentClientSecret = null;
        if (isset($createSubscriptionPlan['latest_invoice']) && $createSubscriptionPlan['latest_invoice']) {
            $invoice = $stripeService->retrieveInvoice($createSubscriptionPlan['latest_invoice']);
            if ($invoice && $invoice->status === 'open' && $invoice->payment_intent) {
                $paymentIntent = $stripeService->retrievePaymentIntent($invoice->payment_intent);
                if ($paymentIntent) {
                    $paymentIntentClientSecret = $paymentIntent['client_secret'];
                }
            }
        }

        $setupIntentClientSecret = null;
        if (isset($createSubscriptionPlan['pending_setup_intent']) && $createSubscriptionPlan['pending_setup_intent']) {
            $setupIntent = $stripeService->retrieveSetupIntent($createSubscriptionPlan['pending_setup_intent']);
            if ($setupIntent && isset($setupIntent['client_secret'])) {
                $setupIntentClientSecret = $setupIntent['client_secret'];
            }
        }

        return response()->json([
            'success' => true,
            'pending_setup_intent' => $setupIntentClientSecret,
            'payment_intent_client_secret' => $paymentIntentClientSecret,
        ]);
    }

    /**
     * Save about description
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAboutDescription(Request $request): JsonResponse
    {
        $community = $request->community;
        $description = $request->description;

        if (!$community) {
            return response()->json([
                'success' => false,
                'message' => __('Community not found'),
            ], 400);
        }

        try {
            $community->description = $description;
            $community->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => Community::getCommunity($community->id),
        ]);
    }

    /**
     * Triggered when payment succeeded -> publish the community
     *
     * @param Request $request
     * @param CommunityService $communityService
     * @param MemberService $memberService
     * @return JsonResponse
     */
    public function publish(
        Request $request,
        CommunityService $communityService,
        MemberService $memberService
    ): JsonResponse {
        if (!session()->has('community_data')) {
            return response()->json(['success' => true]);
        }

        $communityData = $request->session()->get('community_data');

        $subscription = $communityData['subscription'];
        $paymentMethod = $communityData['payment_method'];

        if (isset($communityData['id'])) {
            $community = Community::where(['id' => $communityData['id'], 'user_id' => session('user_id')])->first();
            if (!$community) {
                return response()->json([
                    'success' => false,
                    'message' => __('Community not found'),
                ], 400);
            }

            try {
                $community->status = Community::STATUS_PUBLISHED;
                $community->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        } else {
            $communityName = $communityData['name'];
            $url = $communityService->generateUniqUrl($communityName);

            // create community
            $createCommunity = $communityService->createCommunity(
                name: $communityName,
                url: $url,
                userId: session('user_id')
            );

            if ($createCommunity['success'] !== true) {
                return response()->json($createCommunity, 400);
            }

            $community = $createCommunity['community'];

            // send email
            try {
                Mail::to($community->user->email)->send(new CommunityCreatedMail($community));
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }


        // insert user + create subscription
        try {
            $addMemberToCommunity = $memberService->addUserToCommunity(
                communityId: $community->id,
                userId: session('user_id'),
                access: CommunityMember::ACCESS_ALLOWED,
                role: CommunityMember::ROLE_OWNER
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }

        if ($addMemberToCommunity['success'] !== true) {
            return response()->json($addMemberToCommunity, 400);
        }


        // create subscription plan
        try {
            CommunityPlan::where(['community_id' => $community->id])->update([
                'status' => CommunityPlan::STATUS_DROPPED,
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }

        try {
            $plan = CommunityPlan::create([
                'community_id' => $community->id,
                'payment_method_id' => $paymentMethod->id,
                'st_subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'current_period_start' => $subscription->current_period_start ? DatetimeHelper::timestampToDate($subscription->current_period_start) : null,
                'current_period_end' => $subscription->current_period_end ? DatetimeHelper::timestampToDate($subscription->current_period_end) : null,
                'trial_start' => $subscription->trial_start ? DatetimeHelper::timestampToDate($subscription->trial_start) : null,
                'trial_end' => $subscription->trial_end ? DatetimeHelper::timestampToDate($subscription->trial_end) : null,
                'amount' => $subscription->plan->amount,
                'currency' => $subscription->plan->currency,
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }


        // update subscription description + metadata on stripe
        $stripeService = new StripeService(config('payment.stripe.subscriptions_secret_key'));
        $stripeService->updateSubscriptionDescription($subscription->id, [
            'description' => $community->name,
            'cancel_at_period_end' => false,
            'metadata' => [
                'community_id' => $community->id,
            ],
        ]);

        session()->forget('community_data');


        $incubateurCommunity = Community::getIncubateurCommunity();
        if ($incubateurCommunity) {
            $memberService->addUserToCommunity(
                $incubateurCommunity->id,
                session('user_id'),
                CommunityMember::ACCESS_ALLOWED
            );
        }

        return response()->json([
            'success' => true,
            'plan' => $plan,
            'url' => $community->url
        ]);
    }

    /**
     * Triggered when 3DS payment failed on incubateur/start form
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelPlan(Request $request): JsonResponse
    {
        if (!session()->has('community_data')) {
            return response()->json(['success' => true]);
        }
        $communityData = $request->session()->get('community_data');

        try {
            $stripeService = new StripeService(config('payment.stripe.subscriptions_secret_key'));
            $stripeService->cancelSubscription(
                $communityData['subscription']->id
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
