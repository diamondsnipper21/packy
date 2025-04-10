<?php

namespace App\Http\Controllers\App\Posts;

use App\Helpers\TextHelper;
use App\Models\Community;
use App\Models\Medias;
use App\Models\CommunityPost;
use App\Models\CommunityMember;
use App\Models\ElementLike;
use App\Models\Notification;
use App\Models\Poll;
use App\Services\PostService;
use App\Services\NotificationService;
use App\Services\LoggerService;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\App\AppController;
use App\Exceptions\Jobs\SendEmailNotificationForNewPost;

class CommunityPostsController extends AppController
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
     * Approve member request
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function approve(Request $request): JsonResponse
    {
        $post = $request->post;
        if (!$post) {
            return response()->json(['success' => false], 400);
        }

        try {
            $post->visibility = CommunityPost::VISIBILITY_APPROVED;
            $post->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    /**
     * Decline member request
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function decline(Request $request): JsonResponse
    {
        $post = $request->post;
        if (!$post) {
            return response()->json(['success' => false], 400);
        }

        try {
            $post->visibility = CommunityPost::VISIBILITY_REFUSED;
            $post->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function list(Request $request): array
    {
        $communityId = $request->communityId;
        $role = $request->role ?? CommunityMember::ROLE_MEMBER;
        $memberId = $request->memberId ?? 0;
        $selectedCategoryId = $request->selectedCategoryId ?? 0;
        $selectedFilter = $request->selectedFilter ?? 'none';
        $selectedSort = $request->selectedSort ?? 'newest';
        $page = $request->page ?? 0;

        $sortDir = 'desc';
        if ($selectedSort === 'oldest') {
            $sortDir = 'asc';
        }

        $whereArray = [
            'community_id' => $communityId
        ];
        if (!empty($selectedCategoryId)) {
            $whereArray['category_id'] = $selectedCategoryId;
        }

        $commentsPostIds = [];
        if (!empty($selectedFilter)) {
            if ($selectedFilter === 'pinned') {
                $whereArray['pinned'] = 1;
            } elseif ($selectedFilter === 'no-comments') {
                $commentsPostIds = CommunityPost::getCommentsPostIds($communityId);
            }
        }

        $postQuery = CommunityPost::query();
        $postQuery->where($whereArray);
        
        if (CommunityMember::isManager($role)) {
            $postQuery->whereNotIn('visibility', [CommunityPost::VISIBILITY_REFUSED]);
        } else {
            $postQuery->whereNotIn('visibility', [CommunityPost::VISIBILITY_PENDING, CommunityPost::VISIBILITY_REFUSED]);
        }

        if (count($commentsPostIds) > 0) {
            $postQuery->whereNotIn('id', $commentsPostIds);
        }

        $postQuery->with('member');
        $postQuery->with('member.user');
        $postQuery->with('member.groups');
        $postQuery->with('category');
        $postQuery->orderBy('pinned', 'desc');
        $postQuery->orderBy('created_at', $sortDir);

        $posts = $postQuery->paginate(10, ['*'], 'page', $page);

        foreach ($posts as $key => $post) {
            $posts[$key]->number_of_likes = ElementLike::getNumberOfLikeElement($post->id, ElementLike::POST);
            $posts[$key]->is_member_like = ElementLike::isMemberLikeElement($post->id, ElementLike::POST, $memberId);
        }

        return [
            'success' => true,
            'data' => $posts
        ];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function view(Request $request): JsonResponse
    {
        $postUrl = $request->postUrl ?? '';
        $memberId = $request->memberId ?? 0;

        if (!$postUrl) {
            return response()->json(['success' => false, 'message' => __("Post url is empty")], 404);
        }

        $post = CommunityPost::getPostDetailByUrl($postUrl);
        if (!$post) {
            return response()->json(['success' => false, 'message' => __("Post not found")], 404);
        }

        try {
            $post->number_of_likes = ElementLike::getNumberOfLikeElement($post->id, ElementLike::POST);
            $post->is_member_like = ElementLike::isMemberLikeElement($post->id, ElementLike::POST, $memberId);

            foreach ($post->comments as $key => $comment) {
                $post->comments[$key]->number_of_likes = ElementLike::getNumberOfLikeElement($comment->id, ElementLike::COMMENT);
                $post->comments[$key]->is_member_like = ElementLike::isMemberLikeElement($comment->id, ElementLike::COMMENT, $memberId);
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    /**
     * @todo - this method needs to use a Service (PostService maybe)
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
        $sendEmail = $request->sendEmail ?? false;

        $community = Community::find($communityId);
        if (!$community) {
            return response()->json([
                'success' => false,
                'message' => __('Community not found.')
            ]);
        }

        try {
            $post = new CommunityPost();
            $post->member_id = $memberId;
            $post->community_id = $communityId;
            $post->path = $this->postService->generatePostPath($request->path);
            $post->title = $request->title ?? '';
            $post->content = TextHelper::insertMention($content, $mentionedMembers);
            $post->category_id = $request->category_id ?? 0;
            $post->pinned = $request->pinned ?? 0;
            $post->likes = $request->likes ?? null;
            $post->disable_comment = $request->disable_comment ?? 0;
            
            $autoPostApprobation = $request->community->auto_post_approbation;

            if ($autoPostApprobation === Community::AUTO_POST_APPROBATION || CommunityMember::isManager($request->member->role)) {
                $post->visibility = CommunityPost::VISIBILITY_APPROVED;
            } else if ($autoPostApprobation === Community::MANUAL_POST_APPROBATION) {
                $post->visibility = CommunityPost::VISIBILITY_PENDING;
            }
            $post->save();

            $medias = $request->medias ?? [];
            if (!empty($medias)) {
                $this->postService->makePostMediaItems(
                    Medias::OWNER_POST,
                    $post->id,
                    $medias,
                    false
                );
            }

            $polls = $request->polls ?? [];
            $allowMultipleAnswersChecked = $request->allowMultipleAnswersChecked ?? 0;
            if (!empty($polls)) {
                $this->postService->makePostPollItems(
                    Poll::OWNER_POST,
                    $post->id,
                    $polls,
                    $allowMultipleAnswersChecked ? Poll::TYPE_CHECKBOX : Poll::TYPE_RADIO,
                    false
                );
            }

            // Generate notifications for mentioned members
            if (!empty($mentionedMembers)) {
                $summary = substr($post->title, 0, 250);
                $this->notificationService->generateForMention($communityId, $memberId, Notification::OT_MENTION_IN_POST, $post->id, $summary, $content, $mentionedMembers);
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        // send email notification for new post
        if ($sendEmail) {
            $lastSentNotification = $request->lastSentNotification ?? null;

            try {
                $community->last_sent_notification = $lastSentNotification;
                $community->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            dispatch(new SendEmailNotificationForNewPost(
                $post->id,
                $memberId
            ))->onQueue('send-email-for-new-post');
        }

        return response()->json([
            'success' => true,
            'community' => $community,
            'post' => CommunityPost::where('id', $post->id)
                ->with('member')
                ->with('member.user')
                ->with('member.groups')
                ->with('category')
                ->first()
        ]);
    }

    /**
     * Delete posts
     *
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     */
    public function delete(Request $request, MediaService $mediaService): JsonResponse
    {
        $post = $request->post;
        $member = $request->member;
        $communityId = $request->communityId;
        $memberId = $member->id ?? 0;

        $member = CommunityMember::where([
            'community_id' => $communityId,
            'id' => $memberId
        ])->first();

        if (empty($member) || (!CommunityMember::isManager($member->role) && $post->member_id !== $memberId)) {
            return response()->json(['success' => false, 'message' => __("You don't have permission")], 403);
        }

        try {
            $mediaService->deleteMedia(Medias::OWNER_POST, $post->id);
            Poll::where([
                'owner' => Medias::OWNER_POST,
                'owner_id' => $post->id
            ])->delete();
            $post->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $post = $request->post;
        $member = $request->member;
        $communityId = $request->communityId;
        $memberId = $member->id ?? 0;
        $content = $request->content ?? '';
        $mentionedMembers = $request->mentionedMembers ?? [];
        $action = $request->action ?? null;
        $pageId = $request->classroom_lesson_id ?? null;

        $member = CommunityMember::where([
            'community_id' => $communityId,
            'id' => $memberId
        ])->first();

        if (empty($member) || (!CommunityMember::isManager($member->role) && $post->member_id !== $memberId)) {
            return response()->json(['success' => false, 'message' => __("You don't have permission")], 403);
        }

        try {
            $post->path = $this->postService->generatePostPath($request->path);
            $post->title = $request->title ?? '';
            $post->content = TextHelper::insertMention($content, $mentionedMembers);
            $post->category_id = $request->category_id ?? 0;
            $post->pinned = $request->pinned ?? 0;
            $post->disable_comment = $request->disable_comment ?? 0;

            if ($request->isMediaChanged) {
                $this->postService->makePostMediaItems(
                    Medias::OWNER_POST,
                    $post->id,
                    $request->medias,
                    true
                );
            }
            if ($request->isPollChanged) {
                $this->postService->makePostPollItems(
                    Poll::OWNER_POST,
                    $post->id,
                    $request->polls,
                    '',
                    true
                );
            }

            $post->save();

            // Generate notifications for mentioned members
            if (!empty($mentionedMembers)) {
                $summary = substr($post->title, 0, 250);
                $this->notificationService->generateForMention($communityId, $memberId, Notification::OT_MENTION_IN_POST, $post->id, $summary, $content, $mentionedMembers);
            }

            // Pin | Unpin post for page
            if ($pageId > 0 && $post->id > 0) {
                if ($action === CommunityPost::ACTION_PIN_TO_PAGE) {
                    $this->postService->pinPostToPage($pageId, $post->id);
                } else if ($action === CommunityPost::ACTION_UNPIN_FROM_PAGE) {
                    $this->postService->unpinPostFromPage($pageId, $post->id);
                }
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
            'data' => CommunityPost::getPostDetailByUrl($post->path)
        ]);
    }

    /**
     * @param Request $request
     * @param NotificationService $notificationService
     * @return JsonResponse
     */
    public function like(Request $request, NotificationService $notificationService): JsonResponse
    {
        $post = $request->post;
        $memberId = $request->member->id;

        // @todo - duplication with PostCommentsController@like...
        try {
            if ($memberId !== $post->member_id && $post->visibility === CommunityPost::VISIBILITY_APPROVED) {
                $likeStatus = $this->postService->processLikeElement($post->community_id, $memberId, $post->id, ElementLike::POST, $post->member_id);
                $notificationService->makeNotificationForLikes(ElementLike::POST, $post, $memberId, $likeStatus);
            }

            $numberOfLikes = ElementLike::getNumberOfLikeElement($post->id, ElementLike::POST);
            $isMemberLike = ElementLike::isMemberLikeElement($post->id, ElementLike::POST, $request->member->id);
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function votePoll(Request $request): JsonResponse
    {
        $poll = $request->poll;

        try {
            Poll::find($poll['id'])->update(['voted' => $poll['voted']]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Close post from parent page
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function closeFromPage(Request $request): JsonResponse
    {
        $result = ['success' => false];

        $pageId = $request->pageId ?? null;
        $post = $request->post;

        if (!empty($post) && $pageId > 0) {
            $result = $this->postService->unpinPostFromPage($pageId, $post->id);
        }

        return response()->json($result);
    }
}
