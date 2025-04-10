<?php

namespace App\Http\Controllers\App;

use App\Models\CommunityGroups;
use App\Models\CommunityGroupMembers;
use App\Services\LoggerService;
use Illuminate\Http\Request;

class CommunityGroupsController extends AppController
{
    /**
     * @param Request $request
     * @return array
     */
    public function save(Request $request): array
    {
        if ($request->id) {
            $group = CommunityGroups::find($request->id);
            if (!$group) {
                return ['success' => false, 'message' => __('Community group not found')];
            }
        } else {
            $group = new CommunityGroups();
        }

        try {
            $group->community_id = $request->community_id;
            $group->name = $request->name;
            $group->description = $request->description;
            $group->publish = $request->publish;
            $group->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        CommunityGroupMembers::deleteOne($group->id);
        foreach ($request->members as $member) {
            CommunityGroupMembers::createOne($group->id, $member['id']);
        }

        return [
            'success' => true,
            'groups' => CommunityGroups::where(['community_id' => $request->community_id])->with('members')->get()
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function delete(Request $request): array
    {
        try {
            CommunityGroups::where(['community_id' => $request->community_id, 'id' => $request->id])->delete();
            CommunityGroupMembers::deleteOne($request->id);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return [
            'success' => true,
            'groups' => CommunityGroups::where(['community_id' => $request->community_id])->with('members')->get()
        ];
    }
}