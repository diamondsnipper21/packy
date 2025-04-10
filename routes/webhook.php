<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Webhook\StripeWebhookMiddleware;
use App\Http\Controllers\Webhook\StripeWebhookController;

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
*/

// Webhooks
Route::group([
    'prefix' => 'stripe',
    'middleware' => [StripeWebhookMiddleware::class]
], function () {
    Route::post('/subscriptions', [StripeWebhookController::class, 'handleSubscriptions']);
    Route::post('/marketplace', [StripeWebhookController::class, 'handleMarketplace']);
});
