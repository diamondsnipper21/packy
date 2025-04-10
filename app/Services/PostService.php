<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\CommunityLessonPost;
use App\Models\CommunityPost;
use App\Models\ElementLike;
use App\Models\ScheduledPost;
use Illuminate\Support\Str;

class PostService
{
    private MediaService $mediaService;

    /**
     * @param MediaService $mediaService
     */
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Get repeat publish at timestamp
     *
     * @param ScheduledPost $post
     * @param int $publishAtTimestamp
     * @param int $nextScheduledAt
     * @return array
     * @throws \Exception
     */
    private function publishAtTimestampLists(ScheduledPost $post, int $publishAtTimestamp, int $nextScheduledAt = 0): array
    {
        $publishAtTimestampLists = [];

        $repeatEvery = $post->repeat_every;
        $repeatEndAt = $post->repeat_end_at;

        if (!empty($repeatEvery) && $publishAtTimestamp > 0) {
            $arrayRepeatEvery = explode('_', $repeatEvery);
            if (count($arrayRepeatEvery) === 2) {
                $repeatValPrefix = (int)$arrayRepeatEvery[0];
                $repeatValSuffix = $arrayRepeatEvery[1];

                // @todo - duplication with EventService L116
                $intervalTimestamp = 0;
                if ($repeatValSuffix === 'day') {
                    $intervalTimestamp = 24 * 60 * 60;
                } elseif ($repeatValSuffix === 'week') {
                    $intervalTimestamp = 7 * 24 * 60 * 60;
                } elseif ($repeatValSuffix === 'month') {
                    $intervalTimestamp = 30 * 24 * 60 * 60;
                } elseif ($repeatValSuffix === 'year') {
                    $intervalTimestamp = 365 * 24 * 60 * 60;
                }

                // Add publishAt date for recurring post
                if ($nextScheduledAt < $publishAtTimestamp) {
                    $publishAtTimestampLists[] = $publishAtTimestamp;
                }

                if ($intervalTimestamp > 0) {
                    $nextTimestamp = $publishAtTimestamp + $repeatValPrefix * $intervalTimestamp;

                    if (str_starts_with($repeatEndAt, 'on')) {
                        $repeatEndAtOn = str_replace('on_', '', $repeatEndAt);
                        $repeatEndAtTimestamp = (new \DateTime($repeatEndAtOn, new \DateTimeZone($post->publish_timezone)))->getTimestamp();

                        if ($repeatEndAtTimestamp > 0) {
                            while ($nextTimestamp < $repeatEndAtTimestamp) {
                                if ($nextScheduledAt < $nextTimestamp) {
                                    $publishAtTimestampLists[] = $nextTimestamp;
                                }
                                $nextTimestamp += $repeatValPrefix * $intervalTimestamp;
                            }
                        }
                    } elseif (str_starts_with($repeatEndAt, 'after')) {
                        $repeatEndAtAfter = (int)str_replace('after_', '', $repeatEndAt);

                        if ($repeatEndAtAfter > 0) {
                            $count = 0;
                            while ($count < $repeatEndAtAfter) {
                                if ($nextScheduledAt < $nextTimestamp) {
                                    $publishAtTimestampLists[] = $nextTimestamp;
                                }
                                $count++;
                                $nextTimestamp += $repeatValPrefix * $intervalTimestamp;
                            }
                        }
                    }
                }
            }
        } else {
            if ($nextScheduledAt < $publishAtTimestamp) {
                $publishAtTimestampLists[] = $publishAtTimestamp;
            }
        }

        return array_unique($publishAtTimestampLists);
    }

    /**
     * Generate path string
     *
     * @param string $title
     * @return string
     */
    public function generatePostPath(string $title): string
    {
        return Str::slug($title, '-');
    }

    /**
     * @param ScheduledPost $post
     * @return void
     */
    private function makeCommunityPostFromScheduledPost(ScheduledPost $post)
    {
        try {
            $newPost = new CommunityPost();
            $newPost->visibility = CommunityPost::VISIBILITY_APPROVED;
            $newPost->member_id = $post->member_id;
            $newPost->community_id = $post->community_id;
            $newPost->title = $post->title;
            $newPost->content = $post->content;
            $newPost->path = $this->generatePostPath($post->title);
            $newPost->disable_comment = $post->disable_comment;
            $newPost->likes = $post->likes;
            $newPost->category_id = $post->category_id;
            $newPost->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }

    /**
     * Process Schedule Post intervally
     *
     * @return void
     * @throws \Exception
     */
    public function processScheduledPost(): void
    {
        $scheduledPosts = ScheduledPost::where([])->get();

        $processedCount = 0;
        foreach ($scheduledPosts as $post) {
            $currentTime = time();
            $processTime = new \DateTime("now", new \DateTimeZone($post->publish_timezone));
            $publishAtTimestamp = (new \DateTime($post->publish_at, new \DateTimeZone($post->publish_timezone)))->getTimestamp();
            $nextScheduledAt = $post->next_scheduled_at ? (new \DateTime($post->next_scheduled_at, new \DateTimeZone($post->publish_timezone)))->getTimestamp() : 0;

            $publishAtTimestampList = $this->publishAtTimestampLists($post, $publishAtTimestamp, $nextScheduledAt);
            $scheduledAt = count($publishAtTimestampList) ? $publishAtTimestampList[0] : 0;

            if ($scheduledAt && $scheduledAt <= $currentTime) {
                echo "\nCurrent Time " . $currentTime;
                echo "\nScheduled At " . $scheduledAt;

                $this->makeCommunityPostFromScheduledPost($post);
                $post->next_scheduled_at = $processTime->format('Y-m-d H:i:s');
                $post->save();
                $processedCount++;
                echo "\n\t Processed " . $post->id;
            }

            if (!$scheduledAt) {
                $post->delete();
                echo "\n\t Ended " . $post->id;
            }
        }

        echo "\n$processedCount posts are created.\n";
    }

    /**
     * Make media items for Post
     *
     * @param string $owner
     * @param int $ownerId
     * @param array $mediaItems
     * @param bool $isDelete
     * @return bool
     */
    public function makePostMediaItems(string $owner, int $ownerId, array $mediaItems, bool $isDelete): bool
    {
        if ($isDelete) {
            $this->mediaService->deleteMedia($owner, $ownerId);
        }
        foreach ($mediaItems as $media) {
            $this->mediaService->createMedia($media, $owner, $ownerId);
        }
        
        return true;
    }

    /**
     * Make poll items for Post
     *
     * @param string $owner
     * @param int $ownerId
     * @param array $pollItems
     * @param string $pollType
     * @param bool $isDelete
     * @return bool
     */
    public function makePostPollItems(string $owner, int $ownerId, array $pollItems, string $pollType, bool $isDelete): bool
    {
        if ($isDelete) {
            Poll::where([
                'owner' => $owner,
                'owner_id' => $ownerId
            ])->delete();
        }

        $pollData = [];
        foreach ($pollItems as $poll) {
            $current_date_time = date('Y-m-d H:i:s');
            $pollData[] = [
                'owner' => $owner,
                'owner_id' => $ownerId,
                'content' => $poll['content'],
                'type' => empty($pollType) ? $poll['type'] : $pollType,
                'voted' => $poll['voted'],
                'created_at' => $current_date_time,
                'updated_at' => $current_date_time
            ];
        }

        Poll::insert($pollData);

        return true;
    }

    /**
     * @param int $communityId
     * @param int $memberId 
     * @param int $elementId
     * @param int $elementType
     * @param int $ownerId
     * @return int
     */
    public function processLikeElement(int $communityId,  int $memberId, int $elementId, int $elementType, int $ownerId): int
    {
        try {
            $like = ElementLike::firstOrNew([
                'community_id' => $communityId,
                'member_id' => $memberId,
                'element_id' => $elementId,
                'element_type' => $elementType,
                'element_owner_id' => $ownerId
            ]);
            $like->status = $like->status ? 0 : 1;
            $like->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
        
        return $like->status;
    }

    /**
     * @param int $lessonId
     * @param int $postId
     * @return array
     */
    public function pinPostToPage(int $lessonId, int $postId): array
    {
        $existingLessonPost = CommunityLessonPost::where([
            'lesson_id' => $lessonId,
            'post_id' => $postId
        ])->first();

        if (empty($existingLessonPost)) {
            try {
                $lessonPost = new CommunityLessonPost();
                $lessonPost->lesson_id = $lessonId;
                $lessonPost->post_id = $postId;
                $lessonPost->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return ['success' => false];
            }
        }

        return ['success' => true];
    }

    /**
     * @param int $lessonId
     * @param int $postId
     * @return array
     */
    public function unpinPostFromPage(int $lessonId, int $postId): array
    {
        try {
            CommunityLessonPost::where([
                'lesson_id' => $lessonId,
                'post_id' => $postId
            ])->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        return ['success' => true];
    }
}
