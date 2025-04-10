<?php

namespace App\Http\Controllers\App;

use App\Models\AutoDmTemplates;
use App\Models\Community;
use App\Models\CommunityExtensions;
use App\Models\Extensions;
use App\Services\LoggerService;
use Illuminate\Http\Request;

class ExtensionsController extends AppController
{
    /**
     * @param Request $request
     * @return array
     */
    public function get(Request $request): array
    {
        $community = Community::where(['id' => $request->communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        return [
            'success' => true,
            'extensions' => CommunityExtensions::where(['community_id' => $community->id])->with('extension')->orderBy('created_at', 'desc')->get()
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getOne(Request $request): array
    {
        $id = $request->id ?? 0;
        $communityId = $request->communityId ?? 0;

        $community = Community::where(['id' => $communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        $communityExtension = CommunityExtensions::where([
            'id' => $id, 
            'community_id' => $communityId
        ])
        ->with('extension')
        ->with('template')
        ->first();

        if (!$communityExtension) {
            return ['success' => false, 'message' => __('Extension not found')];
        }

        return [
            'success' => true,
            'extension' => $communityExtension
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function save(Request $request): array
    {
        $id = $request->id ?? 0;

        if ($id == 0) {
            $communityExtension = new CommunityExtensions();
        } else {
            $communityExtension = CommunityExtensions::find($id);
            if (!$communityExtension) {
                return ['success' => false, 'message' => __('Extension not found')];
            }
        }

        $active = $request->active ?? CommunityExtensions::TURN_OFF;

        try {
            $communityExtension->active = $active;
            $communityExtension->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        $source = $request->extension ?? [];
        $type = $source['type'] ?? '';

        if ($type === Extensions::TYPE_AUTO_DM) {
            // if extension type is auto_dm, then template will be saved
            $requestTemplate = $request->template ?? [];
            if (!empty($requestTemplate)) {
                $templateId = $requestTemplate['id'] ?? 0;

                if ($templateId == 0) {
                    $extensionTemplate = new AutoDmTemplates();
                } else {
                    $extensionTemplate = AutoDmTemplates::find($templateId);
                    if (!$extensionTemplate) {
                        return ['success' => false, 'message' => __('Auto DM template not found')];
                    }
                }

                try {
                    $extensionTemplate->community_id = $requestTemplate['community_id'] ?? 0;
                    $extensionTemplate->extension_id = $requestTemplate['extension_id'] ?? 0;
                    $extensionTemplate->member_id = $requestTemplate['member_id'] ?? 0;
                    $extensionTemplate->body = $requestTemplate['body'] ?? '';
                    $extensionTemplate->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }
        }

        $extensions = CommunityExtensions::where([
            'community_id' => $communityExtension->community_id
        ])
        ->with('extension')
        ->orderBy('created_at', 'asc')
        ->get();

        return [
            'success' => true,
            'extensions' => $extensions
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function delete(Request $request): array
    {
        try {
            CommunityExtensions::where(['community_id' => $request->community_id, 'id' => $request->id])->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return [
            'success' => true,
            'extensions' => CommunityExtensions::where(['community_id' => $request->community_id])->with('extension')->orderBy('created_at', 'asc')->get()
        ];
    }
}