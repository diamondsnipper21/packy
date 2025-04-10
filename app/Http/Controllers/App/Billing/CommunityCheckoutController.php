<?php

namespace App\Http\Controllers\App\Billing;

use App\Helpers\CurrencyHelper;
use App\Http\Controllers\App\AppController;
use App\Http\Requests\CommunityPricePurchaseRequest;
use App\Mail\Notifications\NewSubscriptionStartedMail;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Members\Subscriptions\MemberSubscriptions;
use App\Models\PaymentMethodMarketplace;
use App\Models\User;
use App\Services\BillingService;
use App\Services\LoggerService;
use App\Services\MemberService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class CommunityCheckoutController extends AppController
{
    /**
     * @param CommunityPricePurchaseRequest $request
     * @param MemberService $memberService
     * @return JsonResponse
     */
    public function checkout(CommunityPricePurchaseRequest $request, MemberService $memberService): JsonResponse
    {
        $postData = $request->validated();

        $user = User::where(['id' => session('user_id')])->first();
        if (!$user) {
            return $this->error(404, [
                'message' => __('User not found.')
            ]);
        }

        $community = Community::where(['id' => $postData['communityId']])->first();
        if (!$community || $community->products->count() === 0) {
            return $this->error(404, [
                'message' => __('Community not found.')
            ]);
        }

        if (!$user->country && isset($postData['country']) && $postData['country']) {
            try {
                $user->country = $postData['country'];
                $user->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return $this->error();
            }
        }

        if (!$community->user->stripeAccount) {
            return $this->error(404, [
                'message' => __('Payment service unavailable. Please try later.')
            ]);
        }

        $stripeService = new StripeService(null, $community->user->stripeAccount->account_id);

        // create customer if necessary
        if (!$user->stripe_customer_id_marketplace) {
            try {
                $customer = $stripeService->createCustomer(
                    email: $user->email,
                    name: $user->name,
                    country: $user->country,
                    ip: $postData['client_ip'],
                    postalCode: $postData['address_zip'] ?? null
                );

                if (!$customer) {
                    return $this->error(400, ['message' => __('Stripe customer not created.')]);
                }

                $user->stripe_customer_id_marketplace = $customer->id;
                $user->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return $this->error();
            }
        }


        // get/create payment method
        if ($request->type === 'stripe_payment_method') {
            // 2. attach the new payment method to customer
            try {
                $paymentMethod = $stripeService->attachMemberPaymentMethod(
                    $user,
                    $postData['paymentMethod']
                );
            } catch (\Stripe\Exception\ApiErrorException $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getError());
                return $this->error(400, [
                    'message' => __('stripe-errors.' . $e->getError()->code)
                ]);
            }
        } else {
            $paymentMethod = PaymentMethodMarketplace::where(['id' => $postData['paymentMethod'], 'user_id' => $user->id])->first();
        }

        if (!$paymentMethod) {
            return $this->error(404, [
                'message' => __('Payment method not found.')
            ]);
        }


        $member = CommunityMember::where([
            'user_id' => session('user_id'),
            'community_id' => $community->id
        ])->first();


        // this member already exists and this is an overdue payment
        if ($member && $member->subscription && $member->subscription->status === MemberSubscriptions::STATUS_OVERDUE) {
            try {
                $stripeSubscription = $stripeService->updateSubscription(
                    $member->subscription->stripe_subscription_id,
                    $paymentMethod->payment_method_id
                );

                $member->subscription->status = MemberSubscriptions::STATUS_ACTIVE;
                $member->subscription->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return $this->error();
            }

            return $this->success([
                'member' => $memberService->getCommunityMemberInfo($community->id, $user->id),
                'overdue' => true
            ]);
        }


        // 3. add member to community
        $addMemberToCommunity = $memberService->addUserToCommunity(
            $community->id,
            $user->id,
            CommunityMember::ACCESS_PENDING
        );

        if ($addMemberToCommunity['success'] !== true) {
            return $this->error(404, [
                'message' => __('Member not created.')
            ]);
        }
        $member = $addMemberToCommunity['member'];


        // 4. create subscription according to selected period time
        try {
            $stripeSubscription = $stripeService->createSubscription(
                community: $community,
                member: $member,
                paymentMethod: $paymentMethod,
                period: $postData['period']
            );

            if (!$stripeSubscription) {
                return $this->error(400, [
                    'message' => __('Subscription not created.')
                ]);
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->error();
        }


        try {
            $member->access = CommunityMember::ACCESS_ALLOWED;
            $member->subscription_id = $stripeSubscription->id;
            $member->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->error();
        }


        // 5. send email to admin
        if (config('app.env') === 'production') {
            try {
                $price = CurrencyHelper::format($stripeSubscription->amount);
                if ($stripeSubscription->period === MemberSubscriptions::PERIOD_YEARLY) {
                    $price .= '/' . __('year', [], $stripeSubscription->user->language);
                } else {
                    $price .= '/' . __('month', [], $stripeSubscription->user->language);
                }

                Mail::to($community->user->email)->send(new NewSubscriptionStartedMail(
                    community: $community,
                    user: $stripeSubscription->user,
                    price: $price
                ));
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }


        $setupIntentClientSecret = null;
        if (isset($stripeSubscription->pending_setup_intent) && $stripeSubscription->pending_setup_intent) {
            $setupIntent = $stripeService->retrieveSetupIntent($stripeSubscription->pending_setup_intent);
            if ($setupIntent && isset($setupIntent['client_secret'])) {
                $setupIntentClientSecret = $setupIntent['client_secret'];
            }
        }


        return $this->success([
            'member' => $memberService->getCommunityMemberInfo($community->id, $user->id),
            'setupIntentClientSecret' => $setupIntentClientSecret
        ]);
    }

    /**
     * @param BillingService $billingService
     * @return JsonResponse
     */
    public function getVatRate(BillingService $billingService): JsonResponse
    {
        $user = User::where(['id' => session('user_id')])->first();
        if ($user) {
            $rate = $billingService->getVatRateByCountry($user->country);
        } else {
            $rate = $billingService::VAT_RATE_IE;
        }

        return $this->success(['rate' => $rate]);
    }
}
