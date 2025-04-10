<?php

namespace App\Http\Controllers\App\Classrooms;

use App\Http\Controllers\App\AppController;
use App\Models\CommunityClassroomLesson;
use App\Models\CommunityClassroomSet;
use App\Models\CommunityMember;
use App\Services\ClassroomService;
use App\Services\LoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class SetsController
 *
 * @package App\Http\Controllers\App
 */
class SetsController extends AppController
{
    private ClassroomService $classroomService;

    public function __construct(ClassroomService $classroomService)
    {
        $this->classroomService = $classroomService;
    }
    
    /**
     * Get set by id
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

        $classroom = $request->classroom;
        $set = $request->set;

        $whereArray = [];
        if (CommunityMember::isManager($role)) {
            $whereArray = [
                'classroom_id' => $classroom->id,
                'set_id' => $set->id,
            ];
        } else {
            $whereArray = [
                'publish' => 1,
                'classroom_id' => $classroom->id,
                'set_id' => $set->id,
            ];
        }

        $navItems = $this->classroomService->getClassroomNavItems($classroom->id, $memberId, $set->id, $whereArray);
        $set->lessons = $navItems['items'];
        $set->access_value_items = $this->classroomService->getAccessValueItems($set->access_type, $set->access_value);

        return response()->json([
            'success' => true,
            'data' => $set
        ]);
    }

    /**
     * Create set
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $classroomId = $request->classroomId;
        try {
            $set = new CommunityClassroomSet();
            $set->classroom_id = $classroomId;

            $lastOrder = $this->classroomService->getLastOrderOfClassroomSet($classroomId);
            $set->order = $lastOrder + 1;

            $set = $this->classroomService->storeClassroomSet($set, $request);
            $set->access_value_items = $this->classroomService->getAccessValueItems($set->access_type, $set->access_value);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $set
        ]);
    }

    /**
     * Update set
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $set = $request->set;
        try {
            $set = $this->classroomService->storeClassroomSet($set, $request);
            $set->save();
            $set->access_value_items = $this->classroomService->getAccessValueItems($set->access_type, $set->access_value);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $set
        ]);
    }

    /**
     * Delete set
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $set = $request->set;
        try {
            CommunityClassroomLesson::where('set_id', $set->id)->delete();
            $set->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Move set
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function move(Request $request): JsonResponse
    {
        $set = $request->set;
        try {
            $direction = $request->direction;
            $nextElem = $this->classroomService->moveClassroomSetNavItem($request->classroomId, $set->order, $direction);

            if (!empty($nextElem)) {
                $nextOrder = $set->order;
                $set->order = $nextElem->order;
                $set->save();

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
                    'type' => 'set',
                    'id' => $set->id,
                    'name' => $set->name,
                    'publish' => $set->publish,
                    'order' => $set->order,
                    'expand' => false
                ],
                'next' => $nextElem,
            ]
        ]);
    }
}
