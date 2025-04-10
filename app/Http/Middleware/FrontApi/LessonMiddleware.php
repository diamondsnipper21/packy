<?php

namespace App\Http\Middleware\FrontApi;

use App\Models\CommunityClassroomLesson;
use App\Models\CommunityMember;
use App\Services\ClassroomService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LessonMiddleware
{
    /**
     * Check if member has permission for community.
     *
     * @param  Request  $request
     * @param  Closure $next
     * 
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $classroomId = $request->classroomId ?? '';
        $lessonId = $request->lessonId;

        $lesson = CommunityClassroomLesson::find($lessonId);
        if (empty($lesson) || $lesson->classroom_id != (int)$classroomId) {
            return response()->json(['success' => false, 'message' => __('Lesson not found')], 404);
        }

        $role = $request->member ? $request->member->role : $request->role;
        if (empty($role)) {
            $role = CommunityMember::ROLE_MEMBER;
        }

        $level = $request->member ? $request->member->level : $request->level;
        if (empty($level)) {
            $level = 1;
        }

        $memberId = $request->member ? $request->member->id : $request->memberId;

        if (!ClassroomService::checkAccessPermissionForObject($lesson->access_type, $lesson->access_value, $memberId, $role)) {
            return response()->json(['success' => false, 'message' => __('You don\'t have permission. (1)')], 403);
        }

        if (!CommunityMember::isManager($role) && $lesson->level > $level) {
            return response()->json(['success' => false, 'message' => __('Your level is not enough to unlock.')], 403);
        }

        $request->merge(['lesson' => $lesson]);
        
        return $next($request);
    }
}
