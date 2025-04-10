<?php

namespace App\Http\Middleware\FrontApi;

use App\Models\CommunityClassroomSet;
use App\Models\CommunityMember;
use App\Services\ClassroomService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CourseSetMiddleware
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
        $setId = (int)$request->setId;

        $set = CommunityClassroomSet::find($setId);

        if ($setId != 0 && (empty($set) || $set->classroom_id != (int)$classroomId)) {
            return response()->json(['success' => false, 'message' => __('Classroom set not found')], 404);
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

        if ($set && !ClassroomService::checkAccessPermissionForObject($set->access_type, $set->access_value, $memberId, $role)) {
            return response()->json(['success' => false, 'message' => __('You don\'t have permission. (3)')], 403);
        }

        if (!CommunityMember::isManager($role) && $set && $set->level > $level){
            return response()->json(['success' => false, 'message' => __('Your level is not enough to unlock.')], 403);
        }

        $request->merge(['set' => $set]);
        
        return $next($request);
    }
}
