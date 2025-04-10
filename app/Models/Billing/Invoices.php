<?php

namespace App\Models\Billing;

use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
    use BelongsToUser;

    public $table = 'invoices';

    protected $fillable = [
        'user_id',
        'community_id',
        'number',
        'amount',
        'tax',
        'tax_rate',
        'currency',
        'data',
        'status'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_PAID = 'paid';
    const STATUS_REFUNDED = 'refunded';
}
