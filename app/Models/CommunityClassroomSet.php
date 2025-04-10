<?php

namespace App\Models;

use App\Services\MemberService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityClassroomSet extends Model
{
    public $table = 'community_classrooms_sets';

    protected $fillable = [
        'classroom_id',
        'name',
        'publish',
        'order',
        'access_type',
        'access_value',
        'level'
    ];

    protected $appends = [];

    public function lessons(): HasMany
    {
        return $this->hasMany(CommunityClassroomLesson::class, 'set_id')
            ->with('resources')
            ->orderBy('order');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(CommunityClassroom::class, 'classroom_id');
    }

    /**
     * Return expand status of this set
     */
    public function getExpandAttribute ()
    {
        $expand = true;

        $collapsedSetIds = session()->get('collapsed_set_ids') ?? [];
        if (in_array($this->id, $collapsedSetIds)) {
            $expand = false;
        }

        return $expand;
    }

    /**
     * Get last order
     *
     * @param int $classroomId
     * @return int
     */
    public static function getLastOrder(int $classroomId): int
    {
        $lastSet = self::where([
            'classroom_id' => $classroomId
        ])
        ->orderBy('order', 'desc')
        ->first();

        $lastOrder = $lastSet->order ?? 0;

        return $lastOrder;
    }

    /**
     * Get next order for move up
     *
     * @param int $classroomId
     * @param int $id
     * @param int $order
     * @return int
     */
    public static function getNextMoveUpOrder(int $classroomId, int $id, int $order): int
    {
        $nextOrder = $order - 1;
        $classroom = CommunityClassroom::find($classroomId);
        $communityId = $classroom->community_id ?? 0;
        if ($communityId) {
            $role = MemberService::getRole($communityId);

            $whereArray = [];
            if (CommunityMember::isManager($role)) {
                $whereArray = [
                    'classroom_id' => $classroomId,
                    ['order', '<', $order]
                ];
            } else {
                $whereArray = [
                    'classroom_id' => $classroomId,
                    'publish' => 1,
                    ['order', '<', $order]
                ];
            }

            $nextClassroom = self::where($whereArray)
            ->whereNotIn('id', [$id])
            ->orderBy('order', 'desc')
            ->first();

            $nextOrder = $nextClassroom->order ?? $order - 1;
        }

        return $nextOrder;
    }

    /**
     * Get next order for move down
     *
     * @param int $classroomId
     * @param int $id
     * @param int $order
     * @return int
     */
    public static function getNextMoveDownOrder(int $classroomId, int $id, int $order): int
    {
        $nextOrder = $order + 1;
        $classroom = CommunityClassroom::find($classroomId);
        $communityId = $classroom->community_id ?? 0;
        if ($communityId) {
            $role = MemberService::getRole($communityId);

            $whereArray = [];
            if (CommunityMember::isManager($role)) {
                $whereArray = [
                    'classroom_id' => $classroomId,
                    ['order', '>', $order]
                ];
            } else {
                $whereArray = [
                    'classroom_id' => $classroomId,
                    'publish' => 1,
                    ['order', '>', $order]
                ];
            }

            $nextClassroom = self::where($whereArray)
            ->whereNotIn('id', [$id])
            ->orderBy('order', 'asc')
            ->first();

            $nextOrder = $nextClassroom->order ?? $order + 1;
        }

        return $nextOrder;
    }

    /**
     * Get count of sets of classroom
     *
     * @param int $classroomId
     * @return int
     */
    public static function getCountOfSetsByClassroomId (int $classroomId): int
    {
        $count = 0;

        $setsQuery = self::where([
            'classroom_id' => $classroomId
        ]);
        if ($setsQuery->count() > 0) {
            $count = $setsQuery->count();
        }

        return $count;
    }

    /**
     * Save classroom set
     *
     * @param array $data
     * @return array
     */
    public static function saveClassroomSet (array $data): array
    {
        $id = $data['id'] ?? 0;
        $classroomId = $data['classroom_id'] ?? 0;
        $name = $data['name'] ?? '';
        $publish = $data['publish'] ?? 0;
        $accessType = $data['access_type'] ?? 'all';
        $accessValue = $data['access_value'] ?? '';
        $lessons = $data['lessons'] ?? [];

        if (empty($name)) {
            return ['success' => false, 'message' => __('Set name should be provided')];
        }

        if (!$classroomId) {
            return ['success' => false, 'message' => __('Classroom does not exist')];
        }

        if ($id == 0) {
            $classroomSet = new CommunityClassroomSet();
            $lastOrder = CommunityClassroomSet::getLastOrder($classroomId);
            $classroomSet->order = $lastOrder + 1;
        } else {
            $classroomSet = CommunityClassroomSet::find($id);
            if (empty($classroomSet)) {
                return ['success' => false, 'message' => __('Community classroom set not found')];
            }
        }

        try {
            $classroomSet->classroom_id = $classroomId;
            $classroomSet->name = $name;
            $classroomSet->publish = $publish;
            $classroomSet->access_type = $accessType;
            $classroomSet->access_value = $accessValue;
            $classroomSet->save();
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        // Handling for lessons when adding new set
        if ($id == 0) {
            if (!empty($lessons)) {
                foreach ($lessons as $lesson) {
                    $lessonId = $lesson['id'] ?? 0;
                    $lessonAccessType = $lesson['access_type'] ?? 'all';
                    $lessonAccessValue = $lesson['access_value'] ?? '';
                    if ($lessonAccessType === 'all') {
                        $lessonAccessValue = '';
                    }

                    $lessonData = [
                        'id' => $lessonId,
                        'classroom_id' => $classroomId,
                        'set_id' => $classroomSet->id,
                        'title' => $lesson['title'] ?? '',
                        'content' => $lesson['content'] ?? '',
                        'media' => $lesson['media'] ?? '',
                        'action_items' => $lesson['action_items'] ?? '',
                        'transcript' => $lesson['transcript'] ?? '',
                        'discuss' => $lesson['discuss'] ?? 0,
                        'publish' => $lesson['publish'] ?? 0,
                        'access_type' => $lessonAccessType,
                        'access_value' => $lessonAccessValue,
                        'resources' => $lesson['resources'] ?? [],
                    ];

                    CommunityClassroomLesson::saveClassroomLesson($lessonData);
                }
            }
        }

        $classroom = CommunityClassroom::where([
            'id' => $classroomId,
        ])
        ->with('sets')
        ->with('lessons')
        ->first();

        return [
            'success' => true,
            'set' => $classroomSet,
            'classroom' => $classroom
        ];
    }
}
