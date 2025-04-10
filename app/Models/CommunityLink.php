<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use Illuminate\Database\Eloquent\Model;

class CommunityLink extends Model
{
    use BelongsToCommunity;

    public $table = 'community_links';

    protected $fillable = [
        'community_id',
        'name',
        'url'
    ];
}