<?php

namespace App\Http\Controllers\App\Billing;

use App\Http\Controllers\App\AppController;
use App\Models\Community;
use App\Services\LoggerService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityInvoicesController extends AppController
{
    /**
     * @param Request $request
     * @param StripeService $stripeService
     * @return JsonResponse
     */
    public function saveInvoiceData(Request $request, StripeService $stripeService): JsonResponse
    {
        $community = Community::where(['id' => $request->communityId])->first();
        if (!$community) {
            return $this->error(400, ['message' => __('Community not found.')]);
        }

        $arrayUpdate = [
            'name' => $request->data['businessName'] ?? $community->user->name,
            'address' => [
                'city' => $request->data['city'] ?? null,
                'country' => $request->data['country'] ?? null,
                'line1' => $request->data['address'] ?? null,
                'postal_code' => $request->data['zipcode'] ?? null,
            ]
        ];

        $stripeService = new StripeService(config('payment.stripe.subscriptions_secret_key'));
        try {
            $stripeService->updateCustomer($community->user->stripe_customer_id, $arrayUpdate);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->error();
        }

        try {
            $community->invoice_data = json_encode($request->data);
            $community->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->error();
        }

        return $this->success(['message' => __('Invoice data saved.')]);
    }
}
