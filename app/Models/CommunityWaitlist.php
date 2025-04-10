<?php

namespace App\Models;

use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class CommunityWaitlist extends Model
{
    use BelongsToUser;

    public $table = 'community_waitlist';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'url'
    ];
}
