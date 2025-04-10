<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use App\Support\Traits\BelongsToMember;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use BelongsToCommunity;
    use BelongsToMember;

    public $table = 'api_keys';

    protected $fillable = [
        'community_id',
        'member_id',
        'api_key',
        'max_requests'
    ];

    protected $hidden = [
        'max_requests'
    ];
}