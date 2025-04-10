<?php

namespace App\Http\Controllers\App;

use App\Models\CommunityClassroom;
use App\Models\CommunityMember;
use App\Models\RewardLevel;
use App\Services\LevelService;
use App\Services\LoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class RewardLevelController
 *
 * @package App\Http\Controllers\App
 */
class RewardLevelController extends AppController
{
    /**
     * View level
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function view(Request $request): JsonResponse
    {
        try {
            $level = RewardLevel::where('id', $request->levelId)->first();
            $level->classrooms = CommunityClassroom::where([
                'community_id' => $request->communityId,
                'level' => $level->level_number
            ])->get();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $level
        ]);
    }

    /**
     * List levels
     *
     * @param Request $request
     * @param LevelService $levelService
     * @return JsonResponse
     */
    public function list(Request $request, LevelService $levelService): JsonResponse
    {
        try {
            $levels = RewardLevel::where('community_id', $request->community->id)->orderBy('level_number', 'asc')->get();
            $total = CommunityMember::where([
                'community_id' => $request->community->id,
                'access' => CommunityMember::ACCESS_ALLOWED
            ])->count();

            foreach ($levels as $key => $level) {
                $levels[$key]->member_percent = $levelService->getMemberPercentByLevel($level->community_id, $level->level_number, $total);

                $classrooms = CommunityClassroom::where('community_id', $level->community_id)->where('level', $level->level_number)->get();
                $levels[$key]->number_of_rooms = $classrooms->count();
                $levels[$key]->classrooms = $classrooms;
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $levels,
        ]);
    }

    /**
     * Update level
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $level = RewardLevel::find($request->levelId);
        try {
            $level->name = $request->name;
            $level->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $level
        ]);
    }
}
