<?php

namespace App\Http\Controllers\App\Billing;

use App\Http\Controllers\App\AppController;
use App\Http\Requests\CommunityPriceRequest;
use App\Models\Community;
use App\Services\CommunityPriceService;
use App\Services\CommunityService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityPricesController extends AppController
{
    protected StripeService $stripeService;

    public function __construct()
    {
        $this->stripeService = new StripeService();
    }

    /**
     * @param CommunityPriceRequest $request
     * @return JsonResponse
     */
    public function create(CommunityPriceRequest $request, CommunityPriceService $communityPriceService): JsonResponse
    {
        $postData = $request->validated();

        $community = Community::where(['id' => $postData['communityId']])->first();
        if (!$community) {
            return $this->error(400, ['message' => __('Community not found.')]);
        }

        $response = $communityPriceService->create($community, $postData, $this->stripeService);
        if ($response['success'] !== true) {
            return $this->error(400, $response);
        }

        return $this->success($response);
    }

    /**
     * @param CommunityPriceRequest $request
     * @param CommunityPriceService $communityPriceService
     * @return JsonResponse
     */
    public function save(CommunityPriceRequest $request, CommunityPriceService $communityPriceService): JsonResponse
    {
        $postData = $request->validated();

        $community = Community::where(['id' => $postData['communityId']])->with('products')->first();
        if (!$community) {
            return $this->error(400, ['message' => __('Community not found.')]);
        }

        $response = $communityPriceService->update($community, $postData, $this->stripeService);
        if ($response['success'] !== true) {
            return $this->error(400, $response);
        }

        return $this->success($response);
    }

    /**
     * @param Request $request
     * @param CommunityService $communityService
     * @return JsonResponse
     */
    public function updateFreeTrialDays(Request $request, CommunityService $communityService): JsonResponse
    {
        $community = Community::where(['id' => $request->communityId])->first();
        if (!$community) {
            return $this->error(400, ['message' => __('Community not found.')]);
        }

        $response = $communityService->updateFreeTrialDays($community, $request->trialDays);
        if ($response['success'] !== true) {
            return $this->error(400, $response);
        }

        return $this->success($response);
    }
}
