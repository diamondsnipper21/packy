<?php

namespace App\Http\Controllers\App;

use App\Models\CommunityPlan;
use App\Models\UserPaymentMethod;
use App\Models\UserPlansTransactions;
use App\Services\LoggerService;
use App\Services\PlanService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PlansController
 *
 * @package App\Http\Controllers\App
 */
class PlansController extends AppController
{
    /**
     * Get Community Plan
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function view(Request $request): JsonResponse
    {
        if (session('user_id') !== $request->community->user_id) {
            return response()->json(['success' => false, 'message' => __('You don\'t have permission.')], 403);
        }

        $plan = CommunityPlan::where('community_id', $request->community->id)
            ->whereNotIn('status', [CommunityPlan::STATUS_DROPPED, CommunityPlan::STATUS_INCOMPLETE_EXPIRED])
            ->with('payment_method')
            ->with('transactions')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $plan
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCard(Request $request): JsonResponse
    {
        if (session('user_id') !== $request->community->user_id) {
            return response()->json(['success' => false, 'message' => __('You don\'t have permission.')], 403);
        }

        $member = $request->member;

        $plan = CommunityPlan::where('community_id', $request->community->id)
            ->whereNotIn('status', [CommunityPlan::STATUS_DROPPED, CommunityPlan::STATUS_INCOMPLETE_EXPIRED])
            ->with('payment_method')
            ->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'data' => $plan,
                'message' => __('Community plan does not exist.')
            ], 404);
        }

        $stripeService = new StripeService(config('payment.stripe.subscriptions_secret_key'));

        // get/create payment method
        if ($request->type === 'stripe_payment_method') {
            try {
                $paymentMethod = $stripeService->attachUserPaymentMethod(
                    user: $member->user,
                    data: $request->paymentMethod
                );
            } catch (\Stripe\Exception\ApiErrorException $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getError());
                return response()->json([
                    'success' => false,
                    'message' => __('stripe-errors.' . $e->getError()->code)
                ], 400);
            }
        } else {
            $paymentMethod = UserPaymentMethod::where(['id' => (int) $request->paymentMethod])->first();
        }

        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => __('Payment method not found.')
            ], 404);
        }


        try {
            $stripeSubscription = $stripeService->updateSubscription(
                $plan->st_subscription_id,
                $paymentMethod->payment_method_id
            );
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        try {
            $plan->payment_method_id = $paymentMethod->id;
            $plan->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }


        $setupIntentClientSecret = null;
        if (isset($stripeSubscription->pending_setup_intent) && $stripeSubscription->pending_setup_intent) {
            $setupIntent = $stripeService->retrieveSetupIntent($stripeSubscription->pending_setup_intent);
            if ($setupIntent && isset($setupIntent['client_secret'])) {
                $setupIntentClientSecret = $setupIntent['client_secret'];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $stripeSubscription,
            'pending_setup_intent' => $setupIntentClientSecret
        ]);
    }

    /**
     * Cancel a community plan
     *
     * @param Request $request
     * @param PlanService $planService
     * @return JsonResponse
     */
    public function cancel(Request $request, PlanService $planService): JsonResponse
    {
        if (session('user_id') !== $request->community->user_id) {
            return response()->json([
                'success' => false,
                'message' => __('You don\'t have permission.')
            ], 403);
        }

        $communityPlan = CommunityPlan::where('community_id', $request->community->id)
            ->whereNotIn('status', [CommunityPlan::STATUS_DROPPED, CommunityPlan::STATUS_INCOMPLETE_EXPIRED])
            ->with('payment_method')
            ->with('community')
            ->first();

        if (!$communityPlan || !$communityPlan->st_subscription_id) {
            return response()->json([
                'success' => false,
                'data' => $communityPlan,
                'message' => __('Community plan does not exist to cancel.')
            ], 404);
        }

        $planService->cancelPlan($communityPlan, $request->reason);

        return response()->json([
            'success' => true,
            'data' => $communityPlan,
        ]);
    }

    /**
     * Get invoices of a community plan
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function invoices(Request $request): JsonResponse
    {
        if (session('user_id') !== $request->community->user_id) {
            return response()->json(['success' => false, 'message' => __('You don\'t have permission.')], 403);
        }

        // @todo - get invoices for all subscriptions, not only active plans
        $plan = CommunityPlan::where('community_id', $request->community->id)
            ->whereNotIn('status', [CommunityPlan::STATUS_DROPPED, CommunityPlan::STATUS_INCOMPLETE_EXPIRED])
            ->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'data' => $plan,
                'message' => __('Community plan does not exist.')
            ], 404);
        }

        try {
            $stripeService = new StripeService(config('payment.stripe.subscriptions_secret_key'));
            $invoices = $stripeService->getInvoicesBySubscription($plan, $request->limit, $request->page);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function invoiceDownload(Request $request): JsonResponse
    {
        $transaction = UserPlansTransactions::where([
            'community_id' => $request->id,
            'invoice' => $request->invoice
        ])->first();

        if (!$transaction) {
            return $this->error(404, [
                'message' => __('Transaction not found.')
            ]);
        }

        try {
            $stripeService = new StripeService(config('payment.stripe.subscriptions_secret_key'));
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
