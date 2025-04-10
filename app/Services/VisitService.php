<?php

namespace App\Services;

use App\Models\Visit;
use App\Helpers\IpHelper;

class VisitService extends Service
{
    /**
     * @param int $communityId
     * @param string $page
     * @return array
     */
    public function save(int $communityId, string $page): array
    {
        try {
            $visit = new Visit();
            $visit->community_id = $communityId;
            $visit->ip_address = IpHelper::getRealIpAddress();
            $visit->page = $page;
            $visit->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true];
    }
}
