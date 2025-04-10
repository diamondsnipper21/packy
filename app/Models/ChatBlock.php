<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatBlock extends Model
{
    public $table = 'chat_blocks';

    protected $fillable = [
        'from_id',
        'to_id'
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

    /**
     * Check block status
     *
     * @param int $fromId
     * @param int $toId
     * @return bool
     */
    public static function checkBlockStatus(int $fromId, int $toId): bool
    {
        $block = false;
        
        $chatBlock = self::where(function ($query) use ($fromId, $toId) {
            $query->where('from_id', $fromId)
            ->where('to_id', $toId);
        })->orWhere(function ($query) use ($fromId, $toId) {
            $query->where('from_id', $toId)
            ->where('to_id', $fromId);
        })
        ->first();

        if (!empty($chatBlock)) {
            $block = true;
        }

        return $block;
    }

    /**
     * Get blocked chat user ids
     *
     * @param int $userId
     * @return array
     */
    public static function getBlockedChatUserIds(int $userId): array
    {
        return self::where(['from_id' => $userId])
            ->pluck('to_id')
            ->toArray();
    }
}
