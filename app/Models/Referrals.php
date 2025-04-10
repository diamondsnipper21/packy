<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referrals extends Model
{
    public $table = 'referrals';

    protected $fillable = [
        'referral_id', // @todo - add missing foreign relation...
        'user_id'
    ];
}
