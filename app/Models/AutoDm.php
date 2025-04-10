<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoDm extends Model
{
    public $table = 'auto_dms';

    protected $fillable = [
        'community_id',
        'template_id',
        'from_id',
        'to_id'
    ];

    /**
     * Get user ids with no auto dm yet
     *
     * @param int $communityId
     * @param int $templateId
     * @param int $fromId
     * 
     * @return array
     */
    public static function getNoAutoDmUserIds(int $communityId = 0, int $templateId = 0, int $fromId = 0): array
    {
        $memberIds = CommunityMember::getMemberIds($communityId);
        $userIds = CommunityMember::where('id', $memberIds['allowed'])
            ->pluck('user_id')
            ->toArray();

        $userIds = array_diff($userIds, [$fromId]);

        $existAutoDmUserIds = self::where([
            'community_id' => $communityId,
            'template_id' => $templateId
        ])
        ->whereIn('from_id', [$fromId])
        ->pluck('to_id')
        ->toArray();

        $existAutoDmUserIds = array_unique($existAutoDmUserIds);

        $toUserIds = array_diff($userIds, $existAutoDmUserIds);

        return array_unique($toUserIds);
    }
}