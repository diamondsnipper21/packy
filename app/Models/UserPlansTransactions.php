<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPlansTransactions extends Model
{
    use BelongsToCommunity;
    use BelongsToUser;

    public $table = 'user_plans_transactions';

    protected $fillable = [
        'user_id',
        'community_id',
        'plan_id',
        'payment_method_id',
        'number',
        'charge',
        'invoice',
        'amount',
        'tax',
        'tax_rate',
        'currency',
        'country',
        'status'
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(CommunityPlan::class, 'plan_id', 'id');
    }
}
