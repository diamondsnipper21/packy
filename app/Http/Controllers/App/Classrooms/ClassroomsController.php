<?php

namespace App\Http\Controllers\App\Classrooms;

use App\Http\Controllers\App\AppController;
use App\Models\CommunityClassroom;
use App\Models\CommunityClassroomLesson;
use App\Models\CommunityClassroomLessonCompleted;
use App\Models\CommunityClassroomSet;
use App\Models\CommunityMember;
use App\Services\LoggerService;
use App\Services\ClassroomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ClassroomsController
 *
 * @package App\Http\Controllers\App
 */
class ClassroomsController extends AppController
{
    private ClassroomService $classroomService;

    public function __construct(ClassroomService $classroomService)
    {
        $this->classroomService = $classroomService;
    }

    /**
     * Get classrooms
     *
     * @param Request $request
     * @return array
     */
    public function list(Request $request): array
    {
        $role = $request->role ?? CommunityMember::ROLE_MEMBER;
        $communityId = $request->communityId ?? 0;
        $memberId = $request->memberId ?? 0;
        $page = $request->page;
        $simple = $request->simple ?? 0;

        $whereArray = [];
        if (CommunityMember::isManager($role)) {
            $whereArray = [
                'community_id' => $communityId
            ];
        } else {
            $whereArray = [
                'publish' => 1,
                'community_id' => $communityId
            ];
        }

        $classrooms = CommunityClassroom::where($whereArray)
            ->orderBy('order', 'asc')
            ->paginate(9, ['*'], 'page', $page);

        if (!$simple) {
            foreach ($classrooms as $key => $room) {
                $classrooms[$key]->completion = $this->classroomService->calculateClassroomCompletion($room, $memberId);
            }
        }

        return [
            'success' => true,
            'data' => $classrooms
        ];
    }

    /**
     * Get classroom by id
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function view(Request $request): JsonResponse
    {
        $classroom = CommunityClassroom::where('id', $request->classroomId)->with('sets')->first();

        $role = $request->member ? $request->member->role : $request->role;
        if (empty($role)) {
            $role = CommunityMember::ROLE_MEMBER;
        }

        $memberId = $request->member ? $request->member->id : $request->memberId;

        $whereArray = [];
        if (CommunityMember::isManager($role)) {
            $whereArray = [
                'classroom_id' => $classroom->id
            ];
        } else {
            $whereArray = [
                'publish' => 1,
                'classroom_id' => $classroom->id
            ];
        }

        $navItems = $this->classroomService->getClassroomNavItems($classroom->id, $memberId, 0, $whereArray);
        $classroom->items = $navItems['items'];
        $classroom->first_lesson = $navItems['first_lesson'];
        $classroom->completion = $this->classroomService->calculateClassroomCompletion($classroom, $memberId);
        $classroom->access_value_items = $this->classroomService->getAccessValueItems($classroom->access_type, $classroom->access_value);

        return response()->json([
            'success' => true,
            'data' => $classroom
        ]);
    }

    /**
     * Create classroom
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $communityId = $request->communityId;
        try {
            $classroom = new CommunityClassroom();
            $classroom->community_id = $communityId;

            $lastOrder = $this->classroomService->getLastOrderOfClassroom($communityId);
            $classroom->order = $lastOrder + 1;

            $classroom = $this->classroomService->storeClassroom($classroom, $request);
            $classroom->access_value_items = $this->classroomService->getAccessValueItems(
                $classroom->access_type,
                $classroom->access_value
            );
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $classroom
        ]);
    }

    /**
     * Update classroom
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $classroom = $request->classroom;
        try {
            $classroom = $this->classroomService->storeClassroom($classroom, $request);
            $classroom->save();
            $classroom->access_value_items = $this->classroomService->getAccessValueItems($classroom->access_type, $classroom->access_value);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $classroom
        ]);
    }

    /**
     * Delete classroom
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $classroom = $request->classroom;
        try {
            CommunityClassroomLesson::where('classroom_id', $classroom->id)->delete();
            CommunityClassroomSet::where('classroom_id', $classroom->id)->delete();
            CommunityClassroomLessonCompleted::where('classroom_id', $classroom->id)->delete();
            $classroom->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Move classroom
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function move(Request $request): JsonResponse
    {
        $classroom = $request->classroom;
        try {
            $direction = $request->direction;
            if ($direction == 'up') {
                $nextItem = CommunityClassroom::where('order', '<', $classroom->order)
                    ->where('community_id', $request->community->id)
                    ->orderBy('order', 'desc')->first();
            } else {
                $nextItem = CommunityClassroom::where('order', '>', $classroom->order)
                    ->where('community_id', $request->community->id)
                    ->orderBy('order', 'asc')->first();
            }
            if (!empty($nextItem)) {
                $nextItemOrder = $nextItem->order;
                $nextItem->order = $classroom->order;
                $nextItem->save();

                $whereArray = [
                    'classroom_id' => $classroom->id
                ];

                $navItems = $this->classroomService->getClassroomNavItems($nextItem->id, $request->member->id, 0, $whereArray);
                $nextItem->items = $navItems['items'];
                $nextItem->first_lesson = $navItems['first_lesson'];

                $classroom->order = $nextItemOrder;
                $classroom->save();
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'current' => $classroom,
                'next' => $nextItem,
            ],
        ]);
    }
}
