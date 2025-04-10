<?php

namespace App\Http\Controllers\App\Posts;

use App\Helpers\TextHelper;
use App\Models\ScheduledPost;
use App\Models\Medias;
use App\Models\Notification;
use App\Models\Poll;
use App\Services\PostService;
use App\Services\LoggerService;
use App\Services\MediaService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\App\AppController;

class SchedulePostsController extends AppController
{
    /**
     * Get posts
     *
     * @param Request $request
     * @return array
     */
    public function list(Request $request): array
    {
        $selectedCategoryId = $request->selectedCategoryId ?? 0;
        $selectedSort = $request->selectedSort ?? 'newest';

        $memberId = $request->member->id;
        $community = $request->community;

        $sortDir = 'desc';
        if ($selectedSort === 'oldest') {
            $sortDir = 'asc';
        }

        $condition = [
            'community_id' => $community->id,
            'member_id' => $memberId,
            'visibility' => ScheduledPost::VISIBILITY_APPROVED
        ];

        if ($selectedCategoryId) {
            $condition['category_id'] = $selectedCategoryId;
        }

        $scheduledPostQuery = ScheduledPost::query();
        $scheduledPostQuery->where($condition);
        $scheduledPostQuery->with('member');
        $scheduledPostQuery->with('member.user');
        $scheduledPostQuery->with('member.groups');
        $scheduledPostQuery->with('category');
        $scheduledPostQuery->orderBy('created_at', $sortDir);
        $scheduledPosts = $scheduledPostQuery->get();

        return [
            'success' => true,
            'data' => $scheduledPosts
        ];
    }

    /**
     * Get post by id
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function view(Request $request): JsonResponse
    {
        try {
            $post = ScheduledPost::getScheduledPostDetailById($request->postId);
            if (empty($post)) {
                return response()->json(['success' => false, 'message' => __("Scheduled Post not found")], 404);
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
     * Create new post
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request, PostService $postService): JsonResponse
    {
        try {
            $post = new ScheduledPost();
            $post->member_id = $request->member->id;
            $post->community_id = $request->communityId;
            $post->path = $request->path;
            $post->title = $request->title ?? '';
            $post->content = $request->content ?? '';
            $post->category_id = $request->category_id ?? 0;
            $post->visibility = ScheduledPost::VISIBILITY_APPROVED;
            $post->pinned = 0;
            $post->disable_comment = 0;
            $post->publish_at = $request->publish_at ?? '';
            $post->publish_timezone = $request->publish_timezone ?? '';
            $post->repeat_end_at = $request->repeat_end_at ?? '';
            $post->repeat_every = $request->repeat_every ?? '';
            $post->repeat_on = $request->repeat_on ?? '';
            $post->send_notification = $request->send_notification ?? 0;
            $post->save();

            $medias = $request->medias ?? [];
            if (!empty($medias)) {
                $postService->makePostMediaItems(
                    Medias::OWNER_SCHEDULED_POST,
                    $post->id,
                    $medias,
                    false
                );
            }

            $polls = $request->polls ?? [];
            $allowMultipleAnswersChecked = $request->allowMultipleAnswersChecked ?? 0;
            if (!empty($polls)) {
                $postService->makePostPollItems(
                    Poll::OWNER_SCHEDULED_POST,
                    $post->id,
                    $polls,
                    $allowMultipleAnswersChecked ? Poll::TYPE_CHECKBOX : Poll::TYPE_RADIO,
                    false
                );
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
            'data' => ScheduledPost::where('id', $post->id)
                ->with('member')
                ->with('member.user')
                ->with('member.groups')
                ->with('category')
                ->first()
        ]);
    }

    /**
     * Delete scheduled posts
     *
     * @param Request $request
     * @param MediaService $mediaService
     * @return JsonResponse
     */
    public function delete(Request $request, MediaService $mediaService): JsonResponse
    {
        try {
            ScheduledPost::where('id', $request->postId)->delete();
            $mediaService->deleteMedia(Medias::OWNER_SCHEDULED_POST, $request->postId);

            Poll::where([
                'owner' => Medias::OWNER_SCHEDULED_POST,
                'owner_id' => $request->postId
            ])->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Update scheduled posts
     *
     * @param Request $request
     * @param PostService $postService
     * @param NotificationService $notificationService
     * @return JsonResponse
     */
    public function update(Request $request, PostService $postService, NotificationService $notificationService): JsonResponse
    {
        try {
            $post = ScheduledPost::where('id', $request->postId)->first();

            $content = $request->content ?? '';
            $mentionedMembers = $request->mentionedMembers ?? [];

            if ($request->scheduledInfo) {
                $post->publish_at = $request->publish_at ?? '';
                $post->publish_timezone = $request->publish_timezone ?? '';
                $post->repeat_end_at = $request->repeat_end_at ?? '';
                $post->repeat_every = $request->repeat_every ?? '';
                $post->repeat_on = $request->repeat_on ?? '';
                $post->send_notification = $request->send_notification ?? 0;
            } else {
                $post->title = $request->title ?? '';
                $post->content = TextHelper::insertMention($content, $mentionedMembers);
                $post->category_id = $request->category_id ?? 0;

                if ($request->isMediaChanged) {
                    $postService->makePostMediaItems(
                        Medias::OWNER_SCHEDULED_POST,
                        $post->id,
                        $request->medias,
                        true
                    );
                }
                if ($request->isPollChanged) {
                    $postService->makePostPollItems(
                        Poll::OWNER_SCHEDULED_POST,
                        $post->id,
                        $request->polls,
                        '',
                        true
                    );
                }
            }
            $post->save();

            // Generate notifications for mentioned members
            if (!empty($mentionedMembers)) {
                $summary = substr($post->title, 0, 250);
                $notificationService->generateForMention($post->community_id, $post->member_id, Notification::OT_MENTION_IN_POST, $post->id, $summary, $content, $mentionedMembers);
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
            'data' => ScheduledPost::getScheduledPostDetailById($post->id)
        ]);
    }
}
