<?php

namespace App\Http\Controllers\App\Classrooms;

use App\Http\Controllers\App\AppController;
use App\Models\CommunityClassroomLesson;
use App\Models\CommunityClassroomLessonCompleted;
use App\Models\CommunityClassroomSet;
use App\Models\CommunityMember;
use App\Models\LessonResources;
use App\Services\ClassroomService;
use App\Services\LoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class LessonsController
 *
 * @package App\Http\Controllers\App
 */
class LessonsController extends AppController
{
    private ClassroomService $classroomService;

    public function __construct(ClassroomService $classroomService)
    {
        $this->classroomService = $classroomService;
    }

    /**
     * Get lesson by id
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function view(Request $request): JsonResponse
    {
        $role = $request->member ? $request->member->role : $request->role;
        if (empty($role)) {
            $role = CommunityMember::ROLE_MEMBER;
        }

        $memberId = $request->member ? $request->member->id : $request->memberId;

        $whereArray = [];
        if (CommunityMember::isManager($role)) {
            $whereArray = [
                'id' => $request->lessonId
            ];
        } else {
            $whereArray = [
                'publish' => 1,
                'id' => $request->lessonId
            ];
        }

        $lesson = CommunityClassroomLesson::where($whereArray)
        ->with('resources')
        ->with('posts')
        ->first();

        if (empty($lesson)) {
            return response()->json(['success' => false, 'message' => __('Community lesson not found.')], 404);
        }

        $completed = CommunityClassroomLessonCompleted::where([
            'lesson_id' => $request->lessonId,
            'member_id' => $memberId
        ])->first();

        $lesson->completed = empty($completed) ? false : true;
        $lesson->access_value_items = $this->classroomService->getAccessValueItems($lesson->access_type, $lesson->access_value);

        return response()->json([
            'success' => true,
            'data' => $lesson
        ]);
    }

    /**
     * Create lesson
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $classroomId = $request->classroomId;
        $setId = $request->setId;
        try {
            $lesson = new CommunityClassroomLesson();
            $lesson->classroom_id = $classroomId;

            $lastOrder = $this->classroomService->getLastOrderOfLesson($classroomId, $setId);
            $lesson->order = $lastOrder + 1;

            $result = $this->classroomService->storeClassroomLesson($lesson, $request);

            if (!$result['success']) {
                return response()->json($result, 403);
            }

            $newLesson = $result['data'];

            $lesson = CommunityClassroomLesson::where([
                'id' => $newLesson->id,
            ])
            ->with('resources')
            ->first();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $lesson
        ]);
    }

    /**
     * Update lesson
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $memberId = $request->member ? $request->member->id : $request->memberId;
        $lesson = $request->lesson;

        try {
            $result = $this->classroomService->storeClassroomLesson($lesson, $request);

            if (!$result['success']) {
                return response()->json($result, 403);
            }

            $lesson = CommunityClassroomLesson::where([
                'id' => $lesson->id,
            ])
            ->with('resources')
            ->first();

            $completed = CommunityClassroomLessonCompleted::where([
                'member_id' => $memberId,
                'classroom_id' => $lesson->classroom_id,
                'lesson_id' => $lesson->id
            ])->first();

            $lesson->completed = empty($completed) ? false : true;
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $lesson
        ]);
    }

    /**
     * Delete lesson
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $lesson = $request->lesson;
        try {
            LessonResources::where([
                'lesson_id' => $lesson->id
            ])->delete();
            $lesson->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Move lesson
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function move(Request $request): JsonResponse
    {
        $lesson = $request->lesson;
        try {
            $direction = $request->direction;
            $setId = empty($lesson->set_id) ? 0 : $lesson->set_id;
            $nextElem = $this->classroomService->moveClassroomSetNavItem($request->classroomId, $lesson->order, $direction, $setId);

            if (!empty($nextElem)) {
                $nextOrder = $lesson->order;
                $lesson->order = $nextElem->order;
                $lesson->save();

                $nextElem->order = $nextOrder;

                $nextItem = ($nextElem->type == 'set') ? CommunityClassroomSet::find($nextElem->id) : CommunityClassroomLesson::find($nextElem->id);
                $nextItem->order = $nextOrder;
                $nextItem->save();
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'current' => [
                    'type' => 'lesson',
                    'id' => $lesson->id,
                    'name' => $lesson->title,
                    'publish' => $lesson->publish,
                    'order' => $lesson->order,
                    'completed' => $lesson->completed,
                    'set_id' => $lesson->set_id    
                ],
                'next' => $nextElem,
            ]
        ]);
    }

    /**
     * Clone lesson
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clone(Request $request): JsonResponse
    {
        $classroomId = $request->classroomId;
        $setId = $request->setId;
        $lesson = $request->lesson;
        try {

            $newLesson = $lesson->replicate();
            $newLesson->title = $lesson->title . " - Duplicated";
            $lastOrder = $this->classroomService->getLastOrderOfLesson($classroomId, $setId);
            $newLesson->order = $lastOrder + 1;
            $newLesson->save();

            $resources = LessonResources::where([
                'lesson_id' => $request->lessonId,
            ])->get();

            foreach ($resources as $item) {
                $newItem = $item->replicate();
                $newItem->lesson_id = $newLesson->id;
                $newItem->save();
            }

            $lesson = CommunityClassroomLesson::where([
                'id' => $newLesson->id,
            ])
                ->with('resources')
                ->first();

            $lesson->completed = false;
            $lesson->access_value_items = $this->classroomService->getAccessValueItems($lesson->access_type, $lesson->access_value);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $lesson
        ]);
    }

    /**
     * Complete lesson
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function complete(Request $request): JsonResponse
    {
        $lesson = $request->lesson;
        $communityId = $request->communityId;
        $memberId = $request->member->id;
        $classroomId = $request->classroomId;
        $completed = false;

        try {
            $completedLesson = CommunityClassroomLessonCompleted::where([
                'member_id' => $memberId,
                'classroom_id' => $classroomId,
                'lesson_id' => $lesson->id
            ])->first();

            if (!empty($completedLesson)) {
                $completedLesson->delete();
            } else {
                $lessonCompleted = new CommunityClassroomLessonCompleted();
                $lessonCompleted->community_id = $communityId;
                $lessonCompleted->member_id = $memberId;
                $lessonCompleted->classroom_id = $classroomId;
                $lessonCompleted->lesson_id = $lesson->id;
                $lessonCompleted->completed_at = date('Y-m-d H:i:s');
                $lessonCompleted->save();
                $completed = true;
            }
            $lesson->completed = $completed;
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'lesson' => $lesson,
                'completion' => $this->classroomService->calculateClassroomCompletion($request->classroom, $request->member->id)
            ],
        ]);
    }

    /**
     * Update resource
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resource_update(Request $request): JsonResponse
    {
        $resourceId = $request->resourceId;
        $type = $request->type ?? '';
        $label = $request->label ?? '';
        $url = $request->url ?? '';

        try {
            if ($resourceId) {
                $resource = LessonResources::find($resourceId);
                if (empty($resource)) {
                    return response()->json(['success' => false, 'message' => __('Resource not found.')], 404);
                }
            } else {
                $resource = new LessonResources();
                $resource->lesson_id = $request->lessonId;
            }

            $resource->type = $type;
            $resource->label = $label;
            $resource->url = $url;
            $resource->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $resource
        ]);
    }

    /**
     * Delete resource
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resource_delete(Request $request): JsonResponse
    {
        $resourceId = $request->resourceId;
        try {
            LessonResources::where([
                'id' => $resourceId
            ])->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get lesson pages of this community
     *
     * @param Request $request
     * @return array
     */
    public function list(Request $request): array
    {
        $community = $request->community;
        $postId = $request->postId ?? 0;
        $action = $request->action ?? null;

        return [
            'success' => true,
            'data' => $this->classroomService->getLessonsOfCommunity($community->id, $postId, $action)
        ];
    }
}
