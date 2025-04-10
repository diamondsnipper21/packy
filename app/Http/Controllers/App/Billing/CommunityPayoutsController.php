<?php

namespace App\Http\Controllers\App\Billing;

use App\Http\Controllers\App\AppController;
use App\Http\Requests\PayoutRequest;
use App\Models\Community;
use App\Services\PayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityPayoutsController extends AppController
{
    /**
     * @param PayoutRequest $request
     * @param PayoutService $payoutService
     * @return JsonResponse
     */
    public function askForPayout(PayoutRequest $request, PayoutService $payoutService): JsonResponse
    {
        $postData = $request->validated();

        $community = Community::where(['id' => $postData['communityId']])->first();
        if (!$community) {
            return $this->error(400, ['message' => __('Community not found.')]);
        }

        $response = $payoutService->requestPayoutForCommunity($community);
        if ($response['success'] !== true) {
            return $this->error(400, $response);
        }

        return $this->success($response);
    }

    /**
     * @param Request $request
     * @param PayoutService $payoutService
     * @return JsonResponse
     */
    public function getPayoutsData(Request $request, PayoutService $payoutService) : JsonResponse
    {
        $community = Community::where(['id' => $request->communityId])->first();
        if (!$community) {
            return $this->error(400, ['message' => __('Community not found.')]);
        }

        [$payouts, $transactions] = $payoutService->getPayoutsDataByYear($community->id, $request->year);

        return $this->success([
            'payouts' => $payouts,
            'transactions' => $transactions
        ]);
    }
}
