<?php

namespace App\Models;

use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeAccount extends Model
{
    use BelongsToUser;

    public $table = 'stripe_accounts';

    protected $fillable = [
        'user_id',
        'account_id',
        'status'
    ];
}
