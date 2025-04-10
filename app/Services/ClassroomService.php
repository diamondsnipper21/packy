<?php

namespace App\Services;

use App\Models\CommunityClassroom;
use App\Models\CommunityClassroomSet;
use App\Models\CommunityClassroomLesson;
use App\Models\CommunityClassroomLessonCompleted;
use App\Models\CommunityGroupMembers;
use App\Models\CommunityGroups;
use App\Models\CommunityMember;
use App\Models\CommunityLessonPost;
use App\Models\CommunityPost;
use App\Models\LessonResources;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class ClassroomService
 *
 * @package App\Services
 */
class ClassroomService
{
    private MemberService $memberService;

    /**
     * @param MemberService $memberService
     */
    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * Calculate the complete rate of classrooms
     *
     * @param CommunityClassroom $classroom
     * @param int $memberId
     * @return int
     */
    public function calculateClassroomCompletion(CommunityClassroom $classroom, int $memberId): int
    {
        $totalCompleted = CommunityClassroomLessonCompleted::where([
            'member_id' => $memberId,
            'classroom_id' => $classroom->id,
        ])->count();

        $totalLessons = CommunityClassroomLesson::where([
            'classroom_id' => $classroom->id,
            'publish' => 1,
        ])->count();

        return $totalLessons == 0 ? 0 : round($totalCompleted * 100 / $totalLessons);
    }

    /**
     * Get last order of classroom in community
     *
     * @param int $communityId
     * @return int
     */
    public function getLastOrderOfClassroom(int $communityId): int
    {
        $lastClassroom = CommunityClassroom::where([
            'community_id' => $communityId,
        ])
            ->orderBy('order', 'desc')
            ->first();

        return $lastClassroom->order ?? 0;
    }

    /**
     * Get last order of set in classroom
     *
     * @param int $classroomId
     * @return int
     */
    public function getLastOrderOfClassroomSet(int $classroomId): int
    {
        $lastSet = CommunityClassroomSet::where([
            'classroom_id' => $classroomId,
        ])
            ->orderBy('order', 'desc')
            ->first();

        $lastOrder = $lastSet->order ?? 0;

        return $lastOrder;
    }

    /**
     * Get last order of lesson in classroom set
     *
     * @param int $classroomId
     * @param int $setId
     * @return int
     */
    public function getLastOrderOfLesson(int $classroomId, int $setId): int
    {
        $lastOrder = 0;
        if (empty($setId)) {
            $lastSet = CommunityClassroomSet::where([
                'classroom_id' => $classroomId,
            ])
                ->orderBy('order', 'desc')
                ->first();
            if (!empty($lastSet)) {
                $lastOrder = $lastSet->order ?? 0;
            }
            $lastLesson = CommunityClassroomLesson::where('classroom_id', $classroomId)
                ->whereNull('set_id')
                ->orderBy('order', 'desc')
                ->first();
            if (!empty($lastLesson)) {
                $order = $lastLesson->order ?? 0;
                $lastOrder = ($lastOrder < $order) ? $order : $lastOrder;
            }
        } else {
            $lastLesson = CommunityClassroomLesson::where([
                'classroom_id' => $classroomId,
                'set_id' => $setId,
            ])
                ->orderBy('order', 'desc')
                ->first();

            $lastOrder = $lastLesson->order ?? 0;
        }

        return $lastOrder;
    }

    /**
     * Check if title is exist already
     *
     * @param int $communityId
     * @param string $title
     * @param int $roomId
     * @return bool
     */
    private function validateClassroomTitle(int $communityId, string $title, int $roomId = 0): bool
    {
        $classroom = CommunityClassroom::where([
            'title' => $title,
            'community_id' => $communityId,
        ])
            ->whereNotIn('id', [$roomId])
            ->first();

        return empty($classroom) ? true : false;
    }

    /**
     * Store/Update Classroom data
     *
     * @param CommunityClassroom $classroom
     * @param Request $request
     * @return CommunityClassroom
     * @throws Exception
     */
    public function storeClassroom(CommunityClassroom $classroom, Request $request): CommunityClassroom
    {
        $communityId = $request->communityId;

        $title = $request->title ?? '';
        $content = $request->content ?? '';
        $publish = $request->publish ?? 0;
        $photo = $request->photo ?? '';
        $media = $request->media ?? '';
        $accessType = $request->access_type ?? 'all';
        $accessValue = $request->access_value ?? '';
        $level = $request->level ?? 1;

        if (empty($classroom->id) && !$this->validateClassroomTitle($communityId, $title)) {
            throw new Exception(__("Classroom title is exist already"));
        }

        if ($accessType === 'all') {
            $accessValue = '';
        }

        try {
            $classroom->title = $title;
            $classroom->content = $content;
            $classroom->photo = $photo;
            $classroom->media = $media;
            $classroom->publish = $publish;
            $classroom->access_type = $accessType;
            $classroom->access_value = $accessValue;
            $classroom->level = $level;
            $classroom->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }

        return $classroom;
    }

    /**
     * Check if title is exist already
     *
     * @param int $roomId
     * @param string $title
     * @param int $setId
     * @return bool
     */
    public function validateClassroomSetTitle(int $roomId, string $title, int $setId = 0): bool
    {
        $set = CommunityClassroomSet::where([
            'name' => $title,
            'classroom_id' => $roomId,
        ])
            ->whereNotIn('id', [$setId])
            ->first();

        return empty($set) ? true : false;
    }

    /**
     * Store/Update ClassroomSet data
     *
     * @param CommunityClassroomSet $set
     * @param Request $request
     * @return CommunityClassroomSet
     * @throws Exception
     */
    public function storeClassroomSet(CommunityClassroomSet $set, Request $request): CommunityClassroomSet
    {
        $classroomId = $request->classroomId;

        $name = $request->name ?? '';
        $publish = $request->publish ?? 0;
        $accessType = $request->access_type ?? 'all';
        $accessValue = $request->access_value ?? '';
        $level = $request->level ?? 1;

        if ($accessType === 'all') {
            $accessValue = '';
        }

        if (!$set->id && !$this->validateClassroomSetTitle($classroomId, $name)) {
            throw new Exception(__("ClassroomSet name is exist already"));
        }

        try {
            $set->name = $name;
            $set->publish = $publish;
            $set->access_type = $accessType;
            $set->access_value = $accessValue;
            $set->level = $level;
            $set->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }

        return $set;
    }

    /**
     * Store/Update ClassroomLesson data
     *
     * @param CommunityClassroomLesson $lesson
     * @param Request $request
     * @return array
     */
    public function storeClassroomLesson(CommunityClassroomLesson $lesson, Request $request): array
    {
        $classroomId = $request->classroomId;
        $setId = empty($request->setId) ? null : $request->setId;

        $title = $request->title ?? '';
        $content = $request->content ?? '';
        $media = $request->media ?? '';
        $actionItems = $request->action_items ?? '';
        $transcript = $request->transcript ?? '';
        $discuss = $request->discuss ?? 0;
        $publish = $request->publish ?? 0;
        $accessType = $request->access_type ?? 'all';
        $accessValue = $request->access_value ?? '';
        $level = $request->level ?? 1;
        $resources = $request->resources ?? [];

        if ($accessType === 'all') {
            $accessValue = '';
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
            $lesson->level = $level;
            $lesson->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        if (!empty($resources)) {
            foreach ($resources as $requestResource) {
                $resourceId = $requestResource['id'];
                if (is_string($resourceId)) {
                    $resourceId = 0;
                }
                
                try {
                    if ($resourceId) {
                        $resource = LessonResources::find($resourceId);
                        if (empty($resource)) {
                            return ['success' => false, 'message' => __('Resource not found.')];
                        }
                    } else {
                        $resource = new LessonResources();
                        $resource->lesson_id = $lesson->id;
                    }

                    $resource->type = $requestResource['type'];
                    $resource->label = $requestResource['label'];
                    $resource->url = $requestResource['url'];
                    $resource->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                }
            }
        }

        return [
            'success' => true,
            'data' => $lesson
        ];
    }

    /**
     * Store/Update ClassroomSet data
     *
     * @param int $roomId
     * @param int $memberId
     * @param int $setId
     * @param array $whereCond
     * @return array
     */
    public function getClassroomNavItems(int $roomId, int $memberId, int $setId, array $whereCond): array
    {
        $completedLessonIds = CommunityClassroomLessonCompleted::where([
            'member_id' => $memberId,
            'classroom_id' => $roomId,
        ])->pluck('lesson_id')->toArray();

        $items = [];
        $firstLesson = null;

        $lessonsQuery = CommunityClassroomLesson::where($whereCond);
        $firstLessonQuery = clone $lessonsQuery;

        // Get first typical lesson of this classroom
        $firstLesson = $firstLessonQuery->orderBy('set_id')->orderBy('order')->first();

        if (empty($setId)) {
            $lessonsQuery = $lessonsQuery->whereNull('set_id');
        }
        $lessons = $lessonsQuery->get();

        foreach ($lessons as $key => $lesson) {
            $lessons[$key]->completed =
                (!empty($completedLessonIds) && in_array($lesson->id, $completedLessonIds)) ? true : false;
            $items[] = [
                'type' => 'lesson',
                'id' => $lesson->id,
                'name' => $lesson->title,
                'publish' => $lesson->publish,
                'order' => $lesson->order,
                'completed' => $lesson->completed,
                'set_id' => $lesson->set_id,
                'classroom_id' => $roomId,
            ];
        }

        if (empty($setId)) {
            $sets = CommunityClassroomSet::where($whereCond)->get();
            foreach ($sets as $set) {
                $items[] = [
                    'type' => 'set',
                    'id' => $set->id,
                    'name' => $set->name,
                    'publish' => $set->publish,
                    'order' => $set->order,
                    'expand' => false,
                    'classroom_id' => $roomId,
                ];
            }
        }

        usort($items, fn($a, $b) => $a['order'] > $b['order'] ? 1 : -1);

        return [
            'items' => $items,
            'first_lesson' => $firstLesson,
        ];
    }

    /**
     * Check Member Role Permission
     *
     * @param string $accessType
     * @param ?string $accessValue
     * @param ?int $memberId
     * @param string $role
     * @return bool
     */
    public static function checkAccessPermissionForObject(
        string $accessType,
        ?string $accessValue,
        ?int $memberId,
        string $role
    ): bool {
        $accessAbility = false;
        if (CommunityMember::isManager($role)) {
            $accessAbility = true;
        } else {
            if ($accessType === 'all') {
                $accessAbility = true;
            } else {
                if ($accessType === 'only_member' && $memberId) {
                    $values = explode(",", $accessValue);

                    $memberIds = [];
                    $groupIds = [];
                    foreach ($values as $id) {
                        $value = trim($id);
                        if (strpos($value, 'group_') !== false) {
                            $groupIds[] = (int)str_replace('group_', '', $value);
                        } else {
                            $memberIds[] = (int)$value;
                        }
                    }
                    if (in_array($memberId, $memberIds)) {
                        $accessAbility = true;
                    } else {
                        $groupMember = CommunityGroupMembers::whereIn('group_id', $groupIds)
                            ->where('member_id', $memberId)->first();
                        if ($groupMember) {
                            $accessAbility = true;
                        }
                    }
                } else {
                    if ($accessType === 'only_level') {
                        // @todo Access Level
                        $accessAbility = true;
                    }
                }
            }
        }

        return $accessAbility;
    }

    /**
     * Get access value items
     *
     * @param string $accessType
     * @param string $accessValue
     * @return array
     */
    public function getAccessValueItems(string $accessType, string $accessValue): array
    {
        if ($accessType !== 'only_member') {
            return [];
        }

        $accessIds = explode(',', $accessValue);

        $memberIds = [];
        $groupIds = [];
        $result = [];

        foreach ($accessIds as $item) {
            if (strpos($item, 'group_') !== false) {
                $groupIds[] = (int)str_replace('group_', '', $item);
            } else {
                $memberIds[] = (int)$item;
            }
        }

        $members = CommunityMember::whereIn('id', $memberIds)->get();
        foreach ($members as $item) {
            $result[] = [
                'id' => $item->id,
                'name' => $item->user->name,
                'avatar' => $this->memberService->getMemberAvatarUrl($item),
            ];
        }

        $groups = CommunityGroups::whereIn('id', $groupIds)->get();
        foreach ($groups as $item) {
            $result[] = [
                'id' => 'group_' . $item->id,
                'name' => $item->name,
                'avatar' => '',
            ];
        }

        return $result;
    }

    /**
     * Move classroom set  nav item order
     *
     * @param int $classroomId
     * @param int $setOrder
     * @param string $direction
     * @param int $setId
     * @return object|null
     */
    public function moveClassroomSetNavItem(int $classroomId, int $setOrder, string $direction, int $setId = 0): ?object
    {
        $operator = $direction == 'up' ? '<' : '>';
        $sortDirection = $direction == 'up' ? 'desc' : 'asc';
        $setWhere = empty($setId) ? '' : ' AND id = 0 ';
        $lessonWhere = empty($setId) ? ' IS NULL ' : " = $setId ";

        $sql = "
        SELECT * FROM (
            SELECT 'set' AS type, id, name, publish, `order`, id AS set_id
            FROM community_classrooms_sets
            WHERE `order` $operator $setOrder AND classroom_id = $classroomId $setWhere
            UNION ALL
            SELECT 'lesson' AS type, id, title AS name, publish, `order`, set_id
            FROM community_classrooms_lessons
            WHERE `order` $operator $setOrder AND classroom_id = $classroomId AND set_id $lessonWhere
        ) AS t
        ORDER BY t.order $sortDirection
        LIMIT 1
        ";

        $nextItem = DB::select($sql);

        return empty($nextItem) ? null : $nextItem[0];
    }

    /**
     * Get all lessons for this community
     *
     * @param int $communityId
     * @param int $postId
     * @param string | null $action
     * @return object
     */
    public function getLessonsOfCommunity(int $communityId, int $postId, ?string $action): object
    {
        $lessonIds = CommunityLessonPost::where([
            'post_id' => $postId,
        ])
            ->pluck('lesson_id')
            ->toArray();

        $query = CommunityClassroomLesson::select(
            'community_classrooms_lessons.*',
            'community_classrooms.title as classroomTitle'
        )
            ->leftJoin('community_classrooms', 'community_classrooms_lessons.classroom_id', '=',
                'community_classrooms.id')
            ->where('community_classrooms.community_id', $communityId);

        if ($action === CommunityPost::ACTION_PIN_TO_PAGE) {
            $query->whereNotIn('community_classrooms_lessons.id', $lessonIds);
        } else {
            if ($action === CommunityPost::ACTION_UNPIN_FROM_PAGE) {
                $query->whereIn('community_classrooms_lessons.id', $lessonIds);
            }
        }

        return $query->get();
    }
}
