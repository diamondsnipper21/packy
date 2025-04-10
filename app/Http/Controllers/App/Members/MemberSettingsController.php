<?php

namespace App\Http\Controllers\App\Members;

use App\Models\ApiKey;
use App\Models\CommunityMemberSetting;
use App\Models\CommunityMember;
use App\Services\LoggerService;
use App\Http\Controllers\App\AppController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Class MemberSettingsController
 *
 * @package App\Http\Controllers\App
 */
class MemberSettingsController extends AppController
{
    /**
     * Get Member Settings
     *
     * @param Request $request
     * @return array
     */
    public function view(Request $request): array
    {
        $memberId = $request->memberId ?? '';
        $community = $request->community;

        $memberSetting = CommunityMemberSetting::where([
            'community_id' => $community->id,
            'member_id' => $memberId
        ])->first();

        if (empty($memberSetting)) {
            return [
                'success' => false,
                'data' => $memberSetting
            ];
        }

        $apiKey = ApiKey::where([
            'community_id' => $community->id,
            'member_id' => $memberId
        ])->first();

        return [
            'success' => true,
            'data' => [
                'setting' => $memberSetting,
                'api_key' => $apiKey
            ]
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function store(Request $request): array
    {
        $input = $request->all();

        try {
            $memberSetting = CommunityMemberSetting::updateOrCreate([
                'member_id' => $request->memberId,
                'community_id' => $request->communityId
            ],[
                'admin_announce' => $input['admin_announce'],
                'event_reminder' => $input['event_reminder'],
                'likes' => $input['likes'],
                'reply' => $input['reply'],
                'popular_interval' => $input['popular_interval'],
                'unread_interval' => $input['unread_interval'],
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        return [
            'success' => true,
            'data' => $memberSetting,
            'message' => __('Notification settings updated!')
        ];
    }

    /**
     * Generate Api Key
     *
     * @param Request $request
     * @return array
     */
    public function generateApiKey(Request $request): array
    {
        $memberId = $request->memberId ?? '';
        $community = $request->community;

        $member = CommunityMember::where([
            'community_id' => $community->id,
            'id' => $memberId
        ])->first();

        if (empty($member) || !CommunityMember::isManager($member->role)) {
            return [
                'success' => false,
                'message' => __('You don\'t have permission.')
            ];
        }

        try {
            $existApiKey = false;
            do{
                $key = 'pk_' . Str::random(32);
                $existApiKey = ApiKey::where(['api_key' => $key]);
            } while(empty($existApiKey));

            $apiKey = ApiKey::firstOrNew([
                'community_id' => $community->id,
                'member_id' => $memberId
            ]);

            // Generate API Key
            $apiKey->api_key = $key;
            $apiKey->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        return [
            'success' => true,
            'data' => $apiKey
        ];
    }
}
