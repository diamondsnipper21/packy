<?php

namespace App\Models;

use App\Services\LoggerService;
use Illuminate\Database\Eloquent\Model;

class Signups extends Model
{
    public $table = 'signups';

    protected $fillable = [
        'community_id',
        'user_id',
        'origin',
        'ip',
        'fbclid',
        'gclid',
        'date'
    ];
}
