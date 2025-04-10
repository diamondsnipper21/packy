<?php

namespace App\Services;

use App\Models\LessonResources;
use App\Models\Community;
use App\Models\CommunityClassroom;
use App\Models\CommunityClassroomLesson;
use App\Models\CommunityGroupMembers;
use App\Models\CommunityMember;

class ResourceFileService
{
    private MemberService $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * @param object $resourceFile
     * @return bool
     */
    public function isPublicResourceFile(object $resourceFile): bool
    {
        $lessonResource = LessonResources::where(['url' => $resourceFile->resource_url()])->with('lesson')->first();
        if ($lessonResource) {
            if (!$lessonResource->lesson) {
                return false;
            }
            $lesson = $lessonResource->lesson;
        } else {
            $lesson = CommunityClassroomLesson::where('media', $resourceFile->resource_url())->first();
            if (!$lesson) {
                return true;
            }
        }

        $classroom = CommunityClassroom::where(['id' => $lesson->classroom_id])->first();
        if (!$classroom) {
            return false;
        }

        // @todo - use relationship of CommunityClassroom
        $community = Community::find($classroom->community_id);
        if (!$community) {
            return false;
        }

        $role = MemberService::getRole($classroom->community_id);
        
        if (CommunityMember::isManager($role)) {
            return true;
        }

        $communityMember = $this->memberService->getMember($classroom->community_id);
        $memberId = $communityMember ? $communityMember->id : 0;
        $memberLevel = $communityMember ? $communityMember->level : -1;

        if ($community->privacy == Community::PRIVACY_PRIVATE && $memberId == 0) {
            return false;
        }

        switch ($lesson->publish) {
            case CommunityClassroomLesson::DRAFT:
                return CommunityMember::isManager($role);
            case CommunityClassroomLesson::PUBLISH:
                switch ($lesson->access_type) {
                    case CommunityClassroomLesson::All:
                        return true;

                    case CommunityClassroomLesson::ONLY_MEMBER:
                        if ($lesson->access_value) {
                            $accessValues = explode(',', $lesson->access_value);
                            $accessUsers = [];
                            $accessGroups = [];
                            foreach ($accessValues as $value) {
                                if (str_contains($value, 'group_')) {
                                    $accessGroups[] = (int) str_replace('group_', '', $value);
                                } else {
                                    $accessUsers[] = (int)$value;
                                }
                            }
                            $accessGroupMember = CommunityGroupMembers::where(['member_id' => $memberId])
                                ->whereIn('group_id', $accessGroups)
                                ->first();
                            return $accessGroupMember || in_array($memberId, $accessUsers);
                        }
                        return false;

                    case CommunityClassroomLesson::ONLY_LEVEL:
                        $baseLevel = (int)($lesson->access_value ?? 0);
                        return $memberLevel >= $baseLevel;
                }
                break;
        }

        return false;
    }
}
