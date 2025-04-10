<?php

namespace App\Http\Controllers\App\Posts;

use App\Helpers\TextHelper;
use App\Models\CommunityMember;
use App\Models\Medias;
use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\ElementLike;
use App\Models\Notification;
use App\Services\LoggerService;
use App\Services\MediaService;
use App\Services\NotificationService;
use App\Services\PostService;
use App\Http\Controllers\App\AppController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PostCommentsController extends AppController
{
    private PostService $postService;
    private NotificationService $notificationService;

    /**
     * @param PostService $postService
     * @param NotificationService $notificationService
     */
    public function __construct(PostService $postService, NotificationService $notificationService)
    {
        $this->postService = $postService;
        $this->notificationService = $notificationService;
    }
    
    /**
     * Create new post comment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $member = $request->member;
        $communityId = $request->communityId;
        $memberId = $member->id ?? 0;
        $content = $request->content ?? '';
        $mentionedMembers = $request->mentionedMembers ?? [];

        if (empty($content)) {
            return response()->json(['success' => false, 'message' => __('Content empty')], 400);
        }

        try {
            $comment = new CommunityPostComment();
            $comment->member_id = $memberId;
            $comment->post_id = $request->post->id;
            $comment->content = TextHelper::insertMention($content, $mentionedMembers);
            $comment->parent_id = $request->parent_id ?? null;
            $comment->save();

            $medias = $request->medias ?? [];
            if (!empty($medias)) {
                $this->postService->makePostMediaItems(
                    Medias::OWNER_COMMENT,
                    $comment->id,
                    $medias,
                    false
                );
            }

            // Get parent owner id
            $parentOwnerId = CommunityPostComment::getParentOwnerMemberId($comment);

            if ($request->post->visibility === CommunityPost::VISIBILITY_APPROVED && $parentOwnerId !== $comment->member_id) {
                // Generate notifications for comment action
                $this->notificationService->generateForComment($request->post, $communityId, $memberId, $parentOwnerId, $comment);
            }

            // Generate notifications for mentioned members
            if (!empty($mentionedMembers)) {
                $summary = substr($content, 0, 250);
                $summary = TextHelper::insertMention($summary, $mentionedMembers);

                $this->notificationService->generateForMention($communityId, $memberId, Notification::OT_MENTION_IN_COMMENT, $comment->id, $summary, $content, $mentionedMembers);
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
            'data' => CommunityPostComment::getCommentDetailById($comment->id)
        ]);
    }

    /**
     * Update post comment
     *
     * @param Request $request
     * @param PostService $postService
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $member = $request->member;
        $communityId = $request->communityId;
        $memberId = $member->id ?? 0;
        $content = $request->content ?? '';
        $mentionedMembers = $request->mentionedMembers ?? [];

        try {
            $comment = CommunityPostComment::where('id', $request->commentId)->first();
            $comment->content = TextHelper::insertMention($content, $mentionedMembers);
            $comment->save();

            $this->postService->makePostMediaItems(
                Medias::OWNER_COMMENT,
                $comment->id,
                $request->medias,
                true
            );

            // Generate notifications for mentioned members
            if (!empty($mentionedMembers)) {
                $summary = substr($content, 0, 250);
                $summary = TextHelper::insertMention($summary, $mentionedMembers);

                $this->notificationService->generateForMention($communityId, $memberId, Notification::OT_MENTION_IN_COMMENT, $comment->id, $summary, $content, $mentionedMembers);
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
            'data' => CommunityPostComment::getCommentDetailById($comment->id)
        ]);
    }

    /**
     * Delete post comment
     *
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     */
    public function delete(Request $request, MediaService $mediaService): JsonResponse
    {
        try {
            CommunityPostComment::where('id', $request->commentId)->delete();
            CommunityPostComment::where([
                'post_id' => $request->post->id,
                'parent_id' => $request->commentId
            ])->delete();

            $mediaService->deleteMedia(Medias::OWNER_COMMENT, $request->commentId);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Like/Dislike comment
     *
     * @param Request $request
     * @param NotificationService $notificationService
     * @return JsonResponse
     */
    public function like(Request $request, NotificationService $notificationService): JsonResponse
    {
        $comment = CommunityPostComment::where('id', $request->commentId)->with('post')->first();
        if (!$comment) {
            return response()->json(['success' => false, 'message' => __("Comment not found")], 404);
        }
        if (!$comment->post) {
            return response()->json(['success' => false, 'message' => __("Post not found")], 404);
        }

        $member = CommunityMember::where(['community_id' => $comment->post->community_id, 'id' => $comment->post->member_id])->first();
        if (!$member) {
            return response()->json(['success' => false, 'message' => __("Member not found")]);
        }

        try {
            $memberId = $request->member->id;
            if ($memberId != $comment->member_id && $comment->post->visibility === CommunityPost::VISIBILITY_APPROVED) {
                $comment->community_id = $comment->post->community_id;
                $likeStatus = $this->postService->processLikeElement($comment->community_id, $memberId, $comment->id, ElementLike::COMMENT, $member->id);
                $notificationService->makeNotificationForLikes(ElementLike::COMMENT, $comment, $memberId, $likeStatus);
            }

            $numberOfLikes = ElementLike::getNumberOfLikeElement($comment->id, ElementLike::COMMENT);
            $isMemberLike = ElementLike::isMemberLikeElement($comment->id, ElementLike::COMMENT, $memberId);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'number_of_likes' => $numberOfLikes,
                'is_member_like' => $isMemberLike
            ]
        ]);
    }
}
