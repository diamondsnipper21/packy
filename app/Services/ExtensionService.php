<?php

namespace App\Services;

use App\Models\AutoDm;
use App\Models\Chat;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityExtensions;
use App\Models\Extensions;
use App\Models\User;

class ExtensionService
{
    public const FOR_COMMUNITY = 'community';
    public const FOR_JOIN_REQUEST = 'join_request';

    /**
     * @param int $communityId
     * @param string $for
     * @param array $toUserIds
     * 
     * @return array
     */
    public function sendAutoDm(int $communityId, string $for, array $toUserIds = []): array
    {
        $extension = Extensions::where(['type' => Extensions::TYPE_AUTO_DM])->first();

        $extensionId = $extension->id ?? 0;

        $communityExtension = CommunityExtensions::where([
            'community_id' => $communityId, 
            'extension_id' => $extensionId, 
            'active' => CommunityExtensions::TURN_ON
        ])
        ->with('template')
        ->first();

        if (!$communityExtension || !$communityExtension->template) {
            return ['success' => false, 'message' => __('Auto DM Extension not found.')];
        }

        $template = $communityExtension->template;

        // Get from Id of Auto DM
        $fromId = 0;
        $fromMember = CommunityMember::find($template->member_id);
        if (!empty($fromMember)) {
            $user = User::find($fromMember->user_id);
            if (!empty($user)) {
                $fromId = $fromMember->user_id;
            }
        }

        // Get to Ids of Auto DM
        if ($fromId) {
            if ($for === self::FOR_COMMUNITY) {
                $toUserIds = AutoDm::getNoAutoDmUserIds($communityId, $template->id, $fromId);
            }
        }

        $community = Community::find($communityId);

        // Get Auto DM body
        $templateBody = $template->body;
        $templateBody = str_replace('[[COMMUNITYNAME]]', $community->name, $templateBody);

        if (!empty($toUserIds)) {
            foreach ($toUserIds as $toId) {
                $this->generateAutoDm($communityId, $fromId, $toId, $template->id, $templateBody);
            }
        } else {
            return ['success' => false, 'message' => __('New member not found.')];
        }

        return ['success' => true, 'message' => __('Auto DM sent successfully.')];
    }

    /**
     * @param int $communityId
     * @param int $fromId
     * @param int $toId
     * @param int $templateId
     * @param string $templateBody
     * 
     * @return array
     */
    private function generateAutoDm(int $communityId, int $fromId, int $toId, int $templateId, string $templateBody): array
    {
        $toName = '';
        $toUser = User::find($toId);
        if (!empty($toUser)) {
            if (!empty($toUser->firstname)) {
                $toName = $toUser->firstname;
            }

            if (empty($toName)) {
                $toName = $toUser->lastname;
            }
        } else {
            return ['success' => false];
        }

        $body = str_replace('[[FIRSTNAME]]', $toName, $templateBody);

        // Generate new Auto DM Chat
        try {
            $chat = new Chat();
            $chat->community_id = $communityId;
            $chat->from_id = $fromId;
            $chat->to_id = $toId;
            $chat->content = $body;
            $chat->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        // Generate new Auto DM
        try {
            $autoDm = new AutoDm();
            $autoDm->community_id = $communityId;
            $autoDm->template_id = $templateId;
            $autoDm->from_id = $fromId;
            $autoDm->to_id = $toId;
            $autoDm->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        return ['success' => true];
    }

    /**
     * @param int $communityId
     * @return void
     */
    public function generateExtensions(int $communityId): void
    {
        $extensionIds = Extensions::all()->pluck('id')->toArray();
        foreach ($extensionIds as $extensionId) {
            try {
                $communityExtension = new CommunityExtensions();
                $communityExtension->community_id = $communityId;
                $communityExtension->extension_id = $extensionId;
                $communityExtension->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }
    }

    /**
     * @param int $communityId
     * @return void
     */
    public function removeExtensions(int $communityId): void
    {
        try {
            CommunityExtensions::where(['community_id' => $communityId])->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }
}
