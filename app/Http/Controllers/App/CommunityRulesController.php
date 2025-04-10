<?php

namespace App\Http\Controllers\App;

use App\Models\CommunityRule;
use App\Services\LoggerService;
use Illuminate\Http\Request;

class CommunityRulesController extends AppController
{
    /**
     * @param Request $request
     * @return array
     */
    public function save(Request $request): array
    {
        if ($request->id) {
            $rule = CommunityRule::find($request->id);
            if (!$rule) {
                return ['success' => false, 'message' => __('Community rule not found')];
            }
        } else {
            $rule = new CommunityRule();
        }

        try {
            $rule->community_id = $request->community_id;
            $rule->title = $request->title;
            $rule->description = $request->description ?? '';
            if (!$request->id) {
                $rule->order = CommunityRule::generateNewRuleOrder($request->community_id);
            }
            $rule->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return [
            'success' => true,
            'rules' => CommunityRule::where(['community_id' => $request->community_id])->orderBy('order', 'asc')->get()
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function delete(Request $request): array
    {
        try {
            CommunityRule::where(['community_id' => $request->community_id, 'id' => $request->id])->delete();
            CommunityRule::reArrangeRuleOrder($request->community_id, $request->order);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return [
            'success' => true,
            'rules' => CommunityRule::where(['community_id' => $request->community_id])->orderBy('order', 'asc')->get()
        ];
    }


    // @todo - refact to move (1 method for both)
    /**
     * @param Request $request
     * @return array
     */
    public function moveUp(Request $request): array
    {
        $id = $request->id ?? 0;
        $communityId = $request->community_id ?? 0;
        $order = (int)($request->order ?? 0);

        $changeOrder = 0;
        if ($order > 1) {
            $changeOrder = $order - 1;
        }

        if ($changeOrder > 0) {
            $changeRule = CommunityRule::where(['community_id' => $communityId, 'order' => $changeOrder])->first();
            if ($changeRule) {
                try {
                    $changeRule->order = $order;
                    $changeRule->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }

            $rule = CommunityRule::where(['community_id' => $communityId, 'id' => $id])->first();
            if ($rule) {
                try {
                    $rule->order = $changeOrder;
                    $rule->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }
        }

        return [
            'success' => true,
            'rules' => CommunityRule::where(['community_id' => $communityId])->orderBy('order', 'asc')->get()
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function moveDown(Request $request): array
    {
        $id = $request->id ?? 0;
        $communityId = $request->community_id ?? 0;
        $order = (int)($request->order ?? 0);

        $changeOrder = 0;
        if ($order > 0) {
            $changeOrder = $order + 1;
        }

        if ($changeOrder > 0) {
            $changeRule = CommunityRule::where(['community_id' => $communityId, 'order' => $changeOrder])->first();
            if ($changeRule) {
                try {
                    $changeRule->order = $order;
                    $changeRule->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }

            $rule = CommunityRule::where(['community_id' => $communityId, 'id' => $id])->first();
            if ($rule) {
                try {
                    $rule->order = $changeOrder;
                    $rule->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }
        }

        return [
            'success' => true,
            'rules' => CommunityRule::where(['community_id' => $communityId])->orderBy('order', 'asc')->get()
        ];
    }
}