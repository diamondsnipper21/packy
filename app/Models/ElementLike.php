<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use App\Support\Traits\BelongsToMember;
use Illuminate\Database\Eloquent\Model;

class ElementLike extends Model
{
    use BelongsToCommunity;
    use BelongsToMember;

    public $table = 'element_likes';

    protected $fillable = [
        'community_id',
        'member_id',
        'element_id',
        'element_type',
        'element_owner_id',
        'status'
    ];

    protected $hidden = [];

    const POST = 0;
    const COMMENT = 1;

    /**
     * @param int $elementId
     * @param int $elementType
     * @return int
     */
    public static function getNumberOfLikeElement(int $elementId, int $elementType): int
    {
        return self::where([
            'element_id' => $elementId,
            'element_type' => $elementType,
            'status' => 1
        ])->count();
    }

    /**
     * @param int $elementId
     * @param int $elementType
     * @param int $memberId
     * @return int
     */
    public static function isMemberLikeElement(int $elementId, int $elementType, int $memberId): int
    {
        return self::where([
            'member_id' => $memberId,
            'element_id' => $elementId,
            'element_type' => $elementType,
            'status' => 1
        ])->count();
    }
}