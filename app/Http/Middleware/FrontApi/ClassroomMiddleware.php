<?php

namespace App\Http\Middleware\FrontApi;

use App\Models\CommunityClassroom;
use App\Models\CommunityMember;
use App\Services\ClassroomService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClassroomMiddleware
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
        $communityId = $request->communityId ?? '';
        $classroomId = $request->classroomId ?? '';

        $classroom = CommunityClassroom::find($classroomId);
        if (empty($classroom) || $classroom->community_id != (int)$communityId) {
            return response()->json(['success' => false, 'message' => __('Classroom not found')], 404);
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

        if (!ClassroomService::checkAccessPermissionForObject($classroom->access_type, $classroom->access_value, $memberId, $role)) {
            return response()->json(['success' => false, 'message' => __('You don\'t have permission. (2)')], 403);
        }

        if (!CommunityMember::isManager($role) && $classroom->level > $level){
            return response()->json(['success' => false, 'message' => __('Your level is not enough to unlock.')], 403);
        }

        $request->merge(['classroom' => $classroom]);
        
        return $next($request);
    }
}
