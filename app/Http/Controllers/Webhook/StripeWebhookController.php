<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\LoggerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Receives Stripe webhook and return response to Stripe
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleSubscriptions(Request $request): JsonResponse
    {
        return $this->handle($request, config('payment.stripe.webhook_key_subscriptions'));
    }

    /**
     * Receives Stripe webhook and return response to Stripe
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleMarketplace(Request $request): JsonResponse
    {
        return $this->handle($request, config('payment.stripe.webhook_key_marketplace'));
    }

    /**
     * Receives Stripe webhook and return response to Stripe
     *
     * @param Request $request
     * @param string $secret
     * @return JsonResponse
     */
    private function handle(Request $request, string $secret): JsonResponse
    {
        try {
            $event = Webhook::constructEvent(
                payload: @file_get_contents('php://input'),
                sigHeader: $_SERVER['HTTP_STRIPE_SIGNATURE'],
                secret: $secret
            );

            $request->event->event_type = $event->type;
            $request->event->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['received' => false], 400);
        }

        return response()->json(['received' => true]);
    }
}
