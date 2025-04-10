<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extensions extends Model
{
    public $table = 'extensions';

    protected $fillable = [
        'name',
        'description',
        'type'
    ];

    // type
    public const TYPE_MEMBERSHIP_QUESTION = 'membership_question';
    public const TYPE_AUTO_DM = 'auto_dm';
    public const TYPE_UNLOCK_CHAT = 'unlock_chat';
    public const TYPE_FACEBOOK_PIXEL = 'facebook_pixel';
}