<?php

namespace App\Strategies\StripeWebhookEvent;

use App\Enum\MemberSubscriptionTransactionStatusEnum;
use App\Models\CommunityPlan;
use App\Models\Members\Subscriptions\MemberSubscriptions;
use App\Models\Members\Subscriptions\MemberSubscriptionsTransactions;
use App\Models\UserPlansTransactions;
use App\Models\WebhookEvent;
use App\Services\LoggerService;
use App\Services\StripeService;

class InvoicePaidStrategy implements StripeWebhookEventStrategyInterface
{
    /**
     * Executes a strategy when an invoice is paid on Stripe.
     *
     * @param WebhookEvent $webhook
     * @return void
     */
    public function execute(WebhookEvent $webhook): void
    {
        $event = json_decode($webhook->body);

        $subscription = MemberSubscriptions::where(['stripe_subscription_id' => $event->data->object->subscription])->first();
        if ($subscription) {
            $this->saveMemberSubscriptionTransaction($event->data->object, $subscription);

            try {
                $subscription->failed_attempts = 0;
                $subscription->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return;
            }

            // @todo email ?
            return;
        }

        $communityPlan = CommunityPlan::where(['st_subscription_id' => $event->data->object->subscription])->first();
        if ($communityPlan) {
            $this->saveUserPlanTransaction($event->data->object, $communityPlan);

            // @todo email ?
        }
    }

    /**
     * @param $event
     * @param MemberSubscriptions $subscription
     * @return void
     */
    private function saveMemberSubscriptionTransaction($event, MemberSubscriptions $subscription): void
    {
        $existingTransaction = MemberSubscriptionsTransactions::where(['invoice' => $event->id])->first();
        if ($existingTransaction) {
            return;
        }

        $taxRate = 0;
        if ($event->subtotal_excluding_tax > 0) {
            $taxRate = $event->tax / $event->subtotal_excluding_tax;
        }

        $stripeService = new StripeService();

        try {
            $transaction = MemberSubscriptionsTransactions::firstOrNew([
                'charge' => $event->charge
            ]);
            $transaction->invoice = $event->id;
            $transaction->member_id = $subscription->member_id;
            $transaction->community_id = $subscription->community_id;
            $transaction->subscription_id = $subscription->id;
            $transaction->payment_method_id = $subscription->payment_method_id;
            $transaction->number = date('ym') . strtoupper(uniqid());
            $transaction->amount = $event->total;
            $transaction->tax = $event->tax;
            $transaction->tax_rate = $taxRate;
            $transaction->fees = $stripeService->calculatePackieFees($event->total);
            $transaction->currency = strtoupper($event->currency);
            $transaction->country = $event->customer_address->country;
            $transaction->status = MemberSubscriptionTransactionStatusEnum::STATUS_COMPLETE;
            $transaction->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return;
        }

        $stripeService = new StripeService();
        $stripeSubscription = $stripeService->getSubscription($subscription->id);
        if (!$stripeSubscription) {
            \Log::error(['saveMemberSubscriptionTransaction subscription not found', $subscription->id]);
            return;
        }

        try {
            $subscription->next_billing_at = $stripeSubscription->current_period_end;
            $subscription->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return;
        }
    }

    /**
     * @param $event
     * @param CommunityPlan $communityPlan
     * @return void
     */
    private function saveUserPlanTransaction($event, CommunityPlan $communityPlan): void
    {
        $existingTransaction = UserPlansTransactions::where(['invoice' => $event->id])->first();
        if ($existingTransaction) {
            return;
        }

        $taxRate = 0;
        if ($event->subtotal_excluding_tax > 0) {
            $taxRate = $event->tax / $event->subtotal_excluding_tax;
        }

        try {
            $transaction = new UserPlansTransactions();
            $transaction->charge = $event->charge;
            $transaction->community_id = $communityPlan->community_id;
            $transaction->plan_id = $communityPlan->id;
            $transaction->payment_method_id = $communityPlan->payment_method_id;
            $transaction->number = date('ym') . strtoupper(uniqid());
            $transaction->invoice = $event->id;
            $transaction->amount = $event->total;
            $transaction->tax = $event->tax;
            $transaction->tax_rate = $taxRate;
            $transaction->currency = strtoupper($event->currency);
            $transaction->country = $event->customer_address->country;
            $transaction->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return;
        }
    }
}