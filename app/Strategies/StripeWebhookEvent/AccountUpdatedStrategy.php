<?php

namespace App\Strategies\StripeWebhookEvent;

use App\Enum\StripeAccountStatusEnum;
use App\Models\StripeAccount;
use App\Models\WebhookEvent;
use App\Services\LoggerService;

class AccountUpdatedStrategy implements StripeWebhookEventStrategyInterface
{
    /**
     * @param WebhookEvent $webhook
     * @return void
     */
    public function execute(WebhookEvent $webhook)
    {
        $event = json_decode($webhook->body);

        if (!$event->data->object->requirements->currently_due) {
            try {
                StripeAccount::where(['account_id' => $event->data->object->id])->update([
                    'status' => StripeAccountStatusEnum::STATUS_ENABLED
                ]);
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }
    }
}