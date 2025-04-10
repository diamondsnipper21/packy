<?php

namespace App\Models;

use App\Services\LoggerService;
use App\Support\Traits\BelongsToMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityGroupMembers extends Model
{
    use BelongsToMember;

    public $table = 'community_group_members';

    protected $fillable = [
        'group_id',
        'member_id',
    ];

    public function group(): BelongsTo
    {
        return $this->BelongsTo(CommunityGroups::class, 'group_id', 'id');
    }

    /**
     * @param int $groupId
     * @return array
     */
    public static function deleteOne(int $groupId): array
    {
        try {
            self::where(['group_id' => $groupId])->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true];
    }

    /**
     * @param int $groupId
     * @param int $memberId
     * @return array
     */
    public static function createOne(int $groupId, int $memberId): array
    {
        try {
            $groupMember = new CommunityGroupMembers();
            $groupMember->group_id = $groupId;
            $groupMember->member_id = $memberId;
            $groupMember->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true];
    }

}
