<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityNotificationsSettings extends Model
{
    public $table = 'community_notifications_settings';

    protected $fillable = [
        'community_id',
        'type'
    ];
}
