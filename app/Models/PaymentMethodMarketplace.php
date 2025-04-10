<?php

namespace App\Models;

use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class PaymentMethodMarketplace extends Model
{
    use BelongsToUser;

    public $table = 'payment_methods_marketplace';

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'last4',
        'card_brand'
    ];

    protected $hidden = [
        'payment_method_id',
    ];
}
