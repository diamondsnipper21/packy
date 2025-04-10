<?php

namespace App\Models;

use App\Services\LoggerService;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    public $table = 'visits';

    protected $fillable = [
        'community_id',
        'ip_address',
        'page'
    ];

    public const TRACKED_PAGES = ['about', 'start'];
}
