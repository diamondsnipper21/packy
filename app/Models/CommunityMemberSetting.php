<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use App\Support\Traits\BelongsToMember;
use Illuminate\Database\Eloquent\Model;

class CommunityMemberSetting extends Model
{
    use BelongsToCommunity;
    use BelongsToMember;

    public $table = 'community_member_settings';

    protected $fillable = [
        'community_id',
        'member_id',
        'popular_interval',
        'unread_interval',
        'likes',
        'reply',
        'admin_announce',
        'event_reminder',
        'api_key',
        'access_limit'
    ];

    public const POPULAR_INTERVAL_OPT_DAILY = 'daily';
    public const POPULAR_INTERVAL_OPT_WEEKLY = 'weekly';
    public const POPULAR_INTERVAL_OPT_14DAYS = '14days';
    public const POPULAR_INTERVAL_OPT_MONTHLY = 'monthly';
    public const POPULAR_INTERVAL_OPT_NEVER = 'never';

    public const UNREAD_INTERVAL_OPT_HOURLY = 'hourly';
    public const UNREAD_INTERVAL_OPT_3_HOURS = '3-hours';
    public const UNREAD_INTERVAL_OPT_6_HOURS = '6-hours';
    public const UNREAD_INTERVAL_OPT_12_HOURS = '12-hours';
    public const UNREAD_INTERVAL_OPT_DAILY = 'daily';
    public const UNREAD_INTERVAL_OPT_2_DAY = '2-day';
    public const UNREAD_INTERVAL_OPT_WEEKLY = 'weekly';
    public const UNREAD_INTERVAL_OPT_NEVER = 'never';

    public const OPT_NOTIFY_YES = 1;
    public const OPT_NOTIFY_NO = 0;
}
