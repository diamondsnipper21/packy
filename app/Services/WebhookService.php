<?php declare(strict_types=1);

namespace App\Services;

use App\Models\WebhookEvent;
use Carbon\Carbon;

class WebhookService
{
    private array $strategies;

    /**
     * @param array $strategies
     */
    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * @return void
     */
    public function processWebhooks(): void
    {
        $webhooks = WebhookEvent::whereNull('treated_at')->orderBy('created_at', 'ASC')->get();
        foreach ($webhooks as $webhook) {
            $this->processWebhook($webhook);
        }
    }

    /**
     * Treats the incoming webhook event using the appropriate strategy based on the event type.
     *
     * @param WebhookEvent $webhook The webhook event to be treated
     * @return void
     */
    private function processWebhook(WebhookEvent $webhook): void
    {
        $strategy = $this->strategies[$webhook->event_type] ?? null;
        if ($strategy) {
            $strategy->execute($webhook);
        }

        try {
            $webhook->treated_at = Carbon::now();
            $webhook->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }
}
