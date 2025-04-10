<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use Illuminate\Database\Eloquent\Model;

class RewardLevel extends Model
{
    use BelongsToCommunity;

    public $table = 'reward_levels';

    protected $fillable = [
        'community_id',
        'name',
        'level_number',
        'goal_point'
    ];
}
