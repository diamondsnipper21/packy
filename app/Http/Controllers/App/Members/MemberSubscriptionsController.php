<?php

namespace App\Http\Controllers\App\Members;

use App\Http\Controllers\App\AppController;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Members\Subscriptions\MemberSubscriptions;
use App\Models\Members\Subscriptions\MemberSubscriptionsCancelRequests;
use App\Models\Members\Subscriptions\MemberSubscriptionsTransactions;
use App\Models\PaymentMethodMarketplace;
use App\Models\User;
use App\Services\LoggerService;
use App\Services\MemberService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberSubscriptionsController extends AppController
{
    /**
     * A community member updated his subscription to another on (e.g. monthly to annually subscription)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upgrade(Request $request): JsonResponse
    {
        // @todo - Http/Requests for $request

        try {
            MemberSubscriptions::where(['id' => $request->id])->update([
                'price_id' => $request->price_id,
                'period' => $request->period
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->error();
        }

        return $this->success();
    }

    /**
     * A community member wants to reactive his subscription
     *
     * @param Request $request
     * @param MemberService $memberService
     * @return JsonResponse
     */
    public function reactivate(Request $request, MemberService $memberService): JsonResponse
    {
        // @todo - Http/Requests for $request

        $community = Community::where(['id' => $request->communityId])->first();
        if (!$community || $community->products->count() === 0) {
            return $this->error(404, [
                'message' => __('Community not found.')
            ]);
        }

        $communityHasMember = CommunityMember::where([
            'community_id' => $community->id,
            'id' => $request->memberId,
        ])->first();

        if (!$communityHasMember || !$communityHasMember->subscription) {
            return $this->error(404, [
                'message' => __('Member not found.')
            ]);
        }

        try {
            $stripeService = new StripeService(null, $community->user->stripeAccount->account_id);
            $stripeService->reactivateSubscription($communityHasMember->subscription->stripe_subscription_id);

            MemberSubscriptionsCancelRequests::where([
                'community_id' => $community->id,
                'member_id' => $communityHasMember->id,
                'subscription_id' => $communityHasMember->subscription_id
            ])->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->error(400, [
                'message' => $e->getMessage()
            ]);
        }

        return $this->success([
            'member' => $memberService->getMember($community->id, $communityHasMember->user_id),
            'message' => __('Subscription reactivated.')
        ]);
    }

    /**
     * @param Request $request
     * @param MemberService $memberService
     * @return JsonResponse
     */
    public function updateCard(Request $request, MemberService $memberService): JsonResponse
    {
        $subscription = MemberSubscriptions::where(['id' => $request->subscriptionId, 'community_id' => $request->communityId])->first();
        if (!$subscription) {
            return $this->error(404, [
                'message' => __('Subscription not found.')
            ]);
        }

        $stripeService = new StripeService();

        // get/create payment method
        if ($request->type === 'stripe_payment_method') {
            try {
                $paymentMethod = $stripeService->attachMemberPaymentMethod(
                    $subscription->member->user,
                    $request->paymentMethod
                );
            } catch (\Stripe\Exception\ApiErrorException $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getError());
                return $this->error(400, [
                    'message' => __('stripe-errors.' . $e->getError()->code)
                ]);
            }
        } else {
            $paymentMethod = PaymentMethodMarketplace::where(['id' => (int) $request->paymentMethod])->first();
        }

        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => __('Payment method not found.')
            ], 400);
        }

        try {
            $stripeSubscription = $stripeService->updateSubscription(
                $subscription->stripe_subscription_id,
                $paymentMethod->payment_method_id
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->error(400, [
                'message' => $e->getMessage()
            ]);
        }

        try {
            $subscription->payment_method_id = $paymentMethod->id;
            $subscription->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->error(400, [
                'message' => $e->getMessage()
            ]);
        }

        $setupIntentClientSecret = null;
        if (isset($stripeSubscription->pending_setup_intent) && $stripeSubscription->pending_setup_intent) {
            $setupIntent = $stripeService->retrieveSetupIntent($stripeSubscription->pending_setup_intent);
            if ($setupIntent && isset($setupIntent['client_secret'])) {
                $setupIntentClientSecret = $setupIntent['client_secret'];
            }
        }

        return $this->success([
            'message' => __('Your payment method has been updated.'),
            'member' => $memberService->getMember($subscription->community_id, $subscription->member->user_id),
            'pending_setup_intent' => $setupIntentClientSecret
        ]);
    }

    /**
     * @param Request $request
     * @param StripeService $stripeService
     * @return JsonResponse
     */
    public function invoiceDownload(Request $request, StripeService $stripeService): JsonResponse
    {
        $transaction = MemberSubscriptionsTransactions::where([
            'community_id' => $request->communityId,
            'invoice' => $request->invoice,
        ])->first();

        if (!$transaction) {
            return $this->error(404, [
                'message' => __('Transaction not found.')
            ]);
        }

        try {
            $invoice = $stripeService->retrieveInvoice($transaction->invoice);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        if (!$invoice) {
            return $this->error(404, [
                'message' => __('Invoice not found.')
            ]);
        }

        return $this->success([
            'download_url' => $invoice->invoice_pdf
        ]);
    }
}
