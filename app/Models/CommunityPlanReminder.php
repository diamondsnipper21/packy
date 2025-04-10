<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityPlanReminder extends Model
{
    use BelongsToCommunity;

    public $table = 'community_plans_reminders';

    protected $fillable = [
        'community_id',
        'plan_id'
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(CommunityPlan::class, 'plan_id', 'id');
    }
}
