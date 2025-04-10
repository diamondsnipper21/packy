<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommunityLessonPost extends Model
{
    public $table = 'community_lessons_posts';

    protected $fillable = [
        'lesson_id',
        'post_id'
    ];

    public function post(): HasOne
    {
        return $this->hasOne(\App\Models\CommunityPost::class, 'id', 'post_id')->with('member')->with('member.user')->with('member.groups');
    }
}
