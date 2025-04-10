<?php

namespace App\Services;

use App\Models\Signups;
use App\Helpers\IpHelper;

class SignupService extends Service
{
    /**
     * Save new signups
     *
     * @param int $communityId
     * @param int $userId
     * @param string $origin
     * @return array
     */
    public function save(int $communityId, int $userId, string $origin): array
    {
        try {
            $signup = new Signups();
            $signup->community_id = $communityId;
            $signup->user_id = $userId;
            $signup->origin = $origin;
            $signup->ip = IpHelper::getRealIpAddress();
            $signup->date = date('Y-m-d H:i:s');
            $signup->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true];
    }
}
