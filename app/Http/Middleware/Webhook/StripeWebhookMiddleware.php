<?php

namespace App\Http\Middleware\Webhook;

use App\Services\LoggerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\WebhookEvent;

class StripeWebhookMiddleware
{
    /**
     * Store stripe webhook event with body and headers.
     *
     * @var Request $request
     * @var Closure $next
     * 
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $headers = collect($request->header())->transform(function ($item) {
            return $item[0];
        });

        $body = $request->all();

        try {
            $event = WebhookEvent::create([
                'source' => 'Stripe',
                'event_type' => $body['type'],
                'body' => json_encode($body),
                'headers' => json_encode($headers),
            ]);
            $request->merge(['event' => $event]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            \Log::error(['StripeWebhookMiddleware@handle Exception', $e->getMessage()]);
        }

        return $next($request);
    }
}
