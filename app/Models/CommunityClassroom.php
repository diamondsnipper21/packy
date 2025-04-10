<?php

namespace App\Models;

use App\Services\MemberService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityClassroom extends Model
{
    public $table = 'community_classrooms';

    protected $fillable = [
        'community_id',
        'title',
        'content',
        'photo',
        'media',
        'publish',
        'order',
        'access_type',
        'access_value',
        'level'
    ];

    protected $appends = [];

    public function sets(): HasMany
    {
        return $this->hasMany(CommunityClassroomSet::class, 'classroom_id')
            ->orderBy('order');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany('App\Models\CommunityClassroomLesson', 'classroom_id')
            ->whereNull('community_classrooms_lessons.set_id')
            ->orderBy('order', 'asc');
    }

    /**
     * Get last order
     *
     * @param int $communityId
     * @return int
     */
    public static function getLastOrder(int $communityId): int
    {
        $lastClassroom = self::where([
            'community_id' => $communityId
        ])
            ->orderBy('order', 'desc')
            ->first();

        return $lastClassroom->order ?? 0;
    }

    /**
     * Get next order for move up
     *
     * @param int $communityId
     * @param int $id
     * @param int $order
     * @return int
     */
    public static function getNextMoveUpOrder(int $communityId, int $id, int $order): int
    {
        $role = MemberService::getRole($communityId);

        $whereArray = [
            'community_id' => $communityId,
            ['order', '<', $order]
        ];

        $whereArray = [];

        if (CommunityMember::isManager($role)) {
            $whereArray = [
                'community_id' => $communityId,
                ['order', '<', $order]
            ];
        } else {
            $whereArray = [
                'community_id' => $communityId,
                'publish' => 1,
                ['order', '<', $order]
            ];
        }

        $nextClassroom = self::where($whereArray)
            ->whereNotIn('id', [$id])
            ->orderBy('order', 'desc')
            ->first();

        $nextOrder = $nextClassroom->order ?? $order - 1;

        return $nextOrder;
    }

    /**
     * Get next order for move down
     *
     * @param int $communityId
     * @param int $id
     * @param int $order
     * @return int
     */
    public static function getNextMoveDownOrder(int $communityId, int $id, int $order): int
    {
        $role = MemberService::getRole($communityId);

        $whereArray = [
            'community_id' => $communityId,
            ['order', '>', $order]
        ];

        $whereArray = [];
        if (CommunityMember::isManager($role)) {
            $whereArray = [
                'community_id' => $communityId,
                ['order', '>', $order]
            ];
        } else {
            $whereArray = [
                'community_id' => $communityId,
                'publish' => 1,
                ['order', '>', $order]
            ];
        }

        $nextClassroom = self::where($whereArray)
            ->whereNotIn('id', [$id])
            ->orderBy('order', 'asc')
            ->first();

        $nextOrder = $nextClassroom->order ?? $order + 1;

        return $nextOrder;
    }
}
