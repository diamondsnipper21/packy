<?php

namespace App\Http\Controllers\App;

use App\Models\CommunityLink;
use App\Services\LoggerService;
use Illuminate\Http\Request;

class CommunityLinksController extends AppController
{
    /**
     * @param Request $request
     * @return array
     */
    public function save(Request $request): array
    {
        if ($request->id) {
            $link = CommunityLink::find($request->id);
            if (!$link) {
                return ['success' => false, 'message' => __('Community link not found')];
            }
        } else {
            $link = new CommunityLink();
        }

        try {
            $link->community_id = $request->community_id;
            $link->name = $request->name;
            $link->url = $request->url;
            $link->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return [
            'success' => true,
            'links' => CommunityLink::where(['community_id' => $request->community_id])->orderBy('created_at', 'asc')->get()
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function delete(Request $request): array
    {
        try {
            CommunityLink::where(['community_id' => $request->community_id, 'id' => $request->id])->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return [
            'success' => true,
            'links' => CommunityLink::where(['community_id' => $request->community_id])->orderBy('created_at', 'asc')->get()
        ];
    }
}