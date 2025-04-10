<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @todo - rename to ChatMessage
 */
class Chat extends Model
{
    public $table = 'chats';

    protected $fillable = [
        'community_id',
        'from_id',
        'to_id',
        'content',
        'read_at'
    ];

    public const FILTER_ALL = 'all';
    public const FILTER_UNREAD = 'unread';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'medias'
    ];

    public function from(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_id', 'id')
            ->with('member');
    }

    public function to(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_id', 'id')
            ->with('member');
    }

    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Medias::class, 'community_chats_medias', 'chat_id', 'media_id')
            ->withTimestamps()->orderBy('created_at');
    }

    /**
     * Get medias of this chat
     *
     * @return object|null
     */
    public function getMediasAttribute(): ?object
    {
        return $this->medias()->get();
    }

    /**
     * Get chat detail messages
     *
     * @param int $fromId
     * @param int $toId
     * @return object|null
     */
    public static function getChatDetailMessages(int $fromId, int $toId): ?object
    {
        $chats = self::where(function ($query) use ($fromId, $toId) {
            $query->where('from_id', $fromId)
            ->where('to_id', $toId);
        })->orWhere(function ($query) use ($fromId, $toId) {
            $query->where('from_id', $toId)
            ->where('to_id', $fromId);
        })
            ->orderBy('created_at', 'asc')
            ->with('from')
            ->with('to')
            ->get();

        // @todo - this update logic should be moves to a service
        foreach ($chats as $chat) {
            if ($chat->read_at === null && $chat->to_id === session('user_id')) {
                try {
                    $chat->read_at = date('Y-m-d H:i:s');
                    $chat->save();
                } catch (\Exception $e) {
                    return null;
                }
            }
        }

        return $chats;
    }

    /**
     * Get chat detail message
     *
     * @param int $chatId
     * @return object|null
     */
    public static function getChatDetailMessage(int $chatId): ?object
    {
        return self::where(['id' => $chatId])
            ->with('from')
            ->with('to')
            ->first();
    }

    /**
     * Get related chat user ids
     *
     * @param int $userId
     * @param string $filter
     * @return array
     */
    public static function getRelatedChatUserIds(int $userId, string $filter): array
    {
        $query1 = self::query()->where(['from_id' => $userId]);
        if ($filter === self::FILTER_UNREAD) {
            $query1->whereNull('read_at')
                ->whereNotIn('from_id', [$userId]);
        }
        $toIds = $query1->pluck('to_id')->toArray();

        $query2 = self::query()->where(['to_id' => $userId]);
        if ($filter === self::FILTER_UNREAD) {
            $query2->whereNull('read_at')
                ->whereNotIn('from_id', [$userId]);
        }
        $fromIds = $query2->pluck('from_id')->toArray();

        return array_unique(array_merge($fromIds, $toIds));
    }

    /**
     * Get unread chat user ids
     *
     * @param int $userId
     * @param int $communityId
     * @param string $timeLimit
     * @return array
     */
    public static function getUnreadChatUserIds(int $userId, int $communityId = 0, string $timeLimit = ''): array
    {
        $query = self::query();
        if ($communityId) {
            $query->where(['community_id' => $communityId]);
        }
        if ($timeLimit) {
            $query->where('created_at', '>=', $timeLimit);
        }

        $fromIds = $query->where(['to_id' => $userId])
            ->whereNull('read_at')
            ->whereNotIn('from_id', [$userId])
            ->pluck('from_id')
            ->toArray();

        $blockedUserIds = ChatBlock::where(['from_id' => $userId])
            ->pluck('to_id')
            ->toArray();

        return array_unique(array_diff($fromIds, $blockedUserIds));
    }

    /**
     * Get last chat info
     *
     * @param object $user
     * @param int $toId
     * @return object
     */
    public static function getLastChat(object $user, int $toId): ?object
    {
        $lastChat = self::where(['from_id' => $user->id, 'to_id' => $toId])
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastChat) {
            return null;
        }

        // Get user gravatar
        $gravatar = '';
        if (!empty($user->member) && !empty($user->member->photo)) {
            $gravatar = $user->member->photo;
        } else {
            $email = strtolower($user->email);
            if (!empty($email)) {
                $gravatar = 'https://www.gravatar.com/avatar/' . md5($email) . '?s=48&d=identicon';
            }
        }
        if (!$gravatar) {
            $gravatar = 'https://wolfeo.me/assets/img/avatars/default.png';
        }

        $lastChat->gravatar = $gravatar;

        // Get user name
        $name = '';
        if (!empty($user->member)) {
            if (!empty($user->member->firstname)) {
                $name .= $user->member->firstname . ' ';
            }
            if (!empty($user->member->lastname)) {
                $name .= $user->member->lastname;
            }
            if (empty($name)) {
                $name = $user->email;
            }
        }

        $lastChat->name = trim($name);

        return $lastChat;
    }
}
