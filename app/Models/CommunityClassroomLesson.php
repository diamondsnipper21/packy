<?php

namespace App\Models;

use App\Services\LoggerService;
use App\Services\MemberService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityClassroomLesson extends Model
{
    public $table = 'community_classrooms_lessons';

    protected $fillable = [
        'classroom_id',
        'set_id',
        'title',
        'content',
        'media',
        'action_items',
        'transcript',
        'discuss',
        'publish',
        'order',
        'access_type',
        'access_value',
        'level'
    ];

    protected $appends = [];

    // publish
    const DRAFT = 0;
    const PUBLISH = 1;

    // access_type
    const All = 'all';
    const ONLY_MEMBER = 'only_member';
    const ONLY_LEVEL = 'only_level';

    /**
     * Relationship to lesson_resources table
     *
     * @return HasMany
     */
    public function resources(): HasMany
    {
        return $this->hasMany(LessonResources::class, 'lesson_id');
    }


    public function posts(): HasMany
    {
        return $this->hasMany(CommunityLessonPost::class, 'lesson_id')
            ->with('post')
            ->orderBy('created_at');
    }

    /**
     * Get last order
     *
     * @param int $classroomId
     * @param int $setId
     * @return int
     */
    public static function getLastOrder(int $classroomId, int $setId): int
    {
        $lastLesson = self::where([
            'classroom_id' => $classroomId,
            'set_id' => $setId
        ])
            ->orderBy('order', 'desc')
            ->first();

        $lastOrder = $lastLesson->order ?? 0;

        return $lastOrder;
    }

    /**
     * Get next order for move up
     *
     * @param int $classroomId
     * @param int $setId
     * @param int $id
     * @param int $order
     * @return int
     */
    public static function getNextMoveUpOrder(int $classroomId, int $setId, int $id, int $order): int
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
                    'set_id' => $setId,
                    ['order', '<', $order],
                ];
            } else {
                $whereArray = [
                    'classroom_id' => $classroomId,
                    'set_id' => $setId,
                    'publish' => 1,
                    ['order', '<', $order],
                ];
            }

            $nextLesson = self::where($whereArray)
                ->whereNotIn('id', [$id])
                ->orderBy('order', 'desc')
                ->first();

            $nextOrder = $nextLesson->order ?? $order - 1;
        }

        return $nextOrder;
    }

    /**
     * Get next order for move down
     *
     * @param int $classroomId
     * @param int $setId
     * @param int $id
     * @param int $order
     * @return int
     */
    public static function getNextMoveDownOrder(int $classroomId, int $setId, int $id, int $order): int
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
                    'set_id' => $setId,
                    ['order', '>', $order],
                ];
            } else {
                $whereArray = [
                    'classroom_id' => $classroomId,
                    'set_id' => $setId,
                    'publish' => 1,
                    ['order', '>', $order],
                ];
            }

            $nextLesson = self::where($whereArray)
                ->whereNotIn('id', [$id])
                ->orderBy('order', 'asc')
                ->first();

            $nextOrder = $nextLesson->order ?? $order + 1;
        }

        return $nextOrder;
    }

    /**
     * Get count of lessons of classroom
     *
     * @param int $classroomId
     * @return int
     */
    public static function getCountOfLessonsByClassroomId(int $classroomId): int
    {
        $count = 0;

        $query = self::where(['classroom_id' => $classroomId])->whereNull('set_id');
        if ($query->count() > 0) {
            $count = $query->count();
        }

        return $count;
    }

    /**
     * @todo - wrong place for save
     * Save classroom lesson
     *
     * @param array $data
     * @return array
     *
     */
    public static function saveClassroomLesson(array $data): array
    {
        $id = $data['id'] ?? 0;
        $classroomId = $data['classroom_id'] ?? 0;
        $setId = $data['set_id'] ?? null;
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';
        $media = $data['media'] ?? '';
        $actionItems = $data['action_items'] ?? '';
        $transcript = $data['transcript'] ?? '';
        $discuss = $data['discuss'] ?? 0;
        $publish = $data['publish'] ?? 0;
        $accessType = $data['access_type'] ?? 'all';
        $accessValue = $data['access_value'] ?? '';
        $resources = $data['resources'] ?? [];

        if (empty($title)) {
            return ['success' => false, 'message' => __('Lesson title should be provided')];
        }

        if (!$classroomId) {
            return ['success' => false, 'message' => __('Classroom does not exist')];
        }

        if ($accessType === 'all') {
            $accessValue = '';
        }

        if ($id == 0) {
            $lesson = new CommunityClassroomLesson();
            $lastOrder = CommunityClassroomLesson::getLastOrder($classroomId, $setId);
            $lesson->order = $lastOrder + 1;
        } else {
            $lesson = CommunityClassroomLesson::find($id);
            if (empty($lesson)) {
                return ['success' => false, 'message' => __('Community classroom lesson not found')];
            }
        }

        try {
            $lesson->classroom_id = $classroomId;
            $lesson->set_id = $setId;
            $lesson->title = $title;
            $lesson->content = $content;
            $lesson->media = $media;
            $lesson->action_items = $actionItems;
            $lesson->transcript = $transcript;
            $lesson->discuss = $discuss;
            $lesson->publish = $publish;
            $lesson->access_type = $accessType;
            $lesson->access_value = $accessValue;
            $lesson->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        $lessonId = $lesson->id;
        $types = LessonResources::getTypes();

        // Save lesson resources when only adding
        if ($id == 0 && !empty($resources)) {
            foreach ($resources as $resource) {
                $type = $resource['type'] ?? '';
                $label = $resource['label'] ?? '';
                $url = $resource['url'] ?? '';

                if (in_array($type, $types)) {
                    try {
                        $newResource = new LessonResources();
                        $newResource->lesson_id = $lessonId;
                        $newResource->type = $type;
                        $newResource->label = $label;
                        $newResource->url = $url;
                        $newResource->save();
                    } catch (\Exception $e) {
                        LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                        return ['success' => false, 'message' => $e->getMessage()];
                    }
                }
            }
        }

        $classroom = CommunityClassroom::where([
            'id' => $classroomId
        ])
            ->with('sets')
            ->with('lessons')
            ->first();

        $lesson = CommunityClassroomLesson::where([
            'id' => $lessonId
        ])
            ->with('resources')
            ->first();

        return [
            'success' => true,
            'lesson' => $lesson,
            'classroom' => $classroom,
        ];
    }
}
