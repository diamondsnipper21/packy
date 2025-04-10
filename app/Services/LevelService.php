<?php

namespace App\Services;

use App\Models\CommunityMember;
use App\Models\RewardLevel;

class LevelService
{
    protected static array $levels = [
        '1' => 0,
        '2' => 5,
        '3' => 20,
        '4' => 50,
        '5' => 150,
        '6' => 300,
        '7' => 500,
        '8' => 1000,
        '9' => 2500,
        '10' => 5000
    ];

    /**
     * @param int $communityId
     * @param int $level
     * @param int $total
     * 
     * @return int
     */
    public function getMemberPercentByLevel(int $communityId, int $level, int $total): int
    {
        $members = CommunityMember::where([
            'community_id' => $communityId,
            'level' => $level,
            'access' => CommunityMember::ACCESS_ALLOWED
        ])->count();

        return $total == 0 ? 0 : (int)($members * 100 / $total);
    }

    /**
     * @param int $communityId
     * @return void
     */
    public function generateLevels(int $communityId): void
    {
        try {
            $data = [];
            foreach (self::$levels as $level => $point) {
                $data[] = [
                    'community_id' => $communityId,
                    'name' => sprintf('Level %s', $level),
                    'level_number' => $level,
                    'goal_point' => $point
                ];
            }

            RewardLevel::insert($data);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }

    /**
     * @param int $communityId
     * @return void
     */
    public function removeLevels(int $communityId): void
    {
        try {
            RewardLevel::where(['community_id' => $communityId])->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }
}
