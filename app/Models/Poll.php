<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    public $table = 'polls';

    protected $fillable = [
        'owner',
        'owner_id',
        'content',
        'type',
        'voted'
    ];

    public const OWNER_POST = 'post';
    public const OWNER_SCHEDULED_POST = 'scheduled_post';

    // To Do
    public const OWNER_LESSON = 'lesson';

    public const TYPE_RADIO = 'radio';
    public const TYPE_CHECKBOX = 'checkbox';

    /* @todo - add foreign relation for polls.owner_id to community_posts
    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'id', 'post_id');
    }
    */

    /**
     * Get count of poll voted members of community post
     *
     * @param int $postId
     * @return int
     */
    public static function getCountOfPollVotedMemberByPostId(int $postId): int
    {
        $votedMembersArray = [];

        $polls = self::where(['owner' => self::OWNER_POST, 'owner_id' => $postId])
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($polls as $poll) {
            if (!empty($poll->voted)) {
                $votedMembersArray = array_unique(array_merge($votedMembersArray, array_filter(explode(',', $poll->voted))));
            }
        }

        return count($votedMembersArray);
    }

    /**
     * @param string $owner
     * @param int $ownerId
     * @return array
     */
    public static function deletePoll(string $owner, int $ownerId): array
    {
        try {
            self::where(['owner' => $owner, 'owner_id' => $ownerId])->delete();
        } catch (\Exception $e) {
            \Log::info(['Poll deletePoll Exception', $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true];
    }

    /**
     * @param array $arrayInsert
     * @param string $owner
     * @param int $ownerId
     * @return array
     */
    public static function createPoll(array $arrayInsert, string $owner, int $ownerId, int $allowMultipleAnswersChecked): array
    {
        extract($arrayInsert);
        
        if ($allowMultipleAnswersChecked) {
            $type = self::TYPE_CHECKBOX;
        }

        if (empty($type)) {
            $type = self::TYPE_RADIO;
        }

        try {
            $poll = new Poll();
            $poll->owner = $owner;
            $poll->owner_id = $ownerId;
            $poll->content = $content;
            $poll->type = $type;
            $poll->voted = $voted;
            $poll->save();
        } catch (\Exception $e) {
            \Log::info(['Poll createPoll Exception', $e->getMessage()]);
            return ['success' => false];
        }

        return ['success' => true];
    }
}
