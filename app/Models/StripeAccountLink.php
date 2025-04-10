<?php

namespace App\Models;

use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeAccountLink extends Model
{
    use BelongsToUser;

    public $table = 'stripe_accounts_links';

    protected $fillable = [
        'user_id',
        'account_id',
        'object',
        'url',
        'expires_at'
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(StripeAccount::class, 'account_id', 'id');
    }
}
