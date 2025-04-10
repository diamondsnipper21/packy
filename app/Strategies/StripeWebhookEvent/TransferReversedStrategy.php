<?php

namespace App\Strategies\StripeWebhookEvent;

use App\Enum\PayoutStatusEnum;
use App\Models\Billing\Payouts;
use App\Models\WebhookEvent;
use App\Services\LoggerService;

class TransferReversedStrategy implements StripeWebhookEventStrategyInterface
{
    /**
     * @param WebhookEvent $webhook
     * @return void
     */
    public function execute(WebhookEvent $webhook): void
    {
        $event = json_decode($webhook->body);

        foreach ($event->data->object->reversals['data'] as $reversal) {
            $payout = Payouts::where(['stripe_transfer_id' => $reversal->transfer])->first();
            if (!$payout) {
                continue;
            }

            try {
                $payout->status = PayoutStatusEnum::STATUS_REVERSED;
                $payout->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return;
            }
        }
    }
}