<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityEvent extends Model
{
    public $table = 'community_events';

    protected $fillable = [
        'community_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'duration',
        'repeat_every',
        'repeat_on',
        'timezone',
        'media',
        'link',
        'type',
        'location',
        'access_type',
        'access_value',
        'level',
        'origin_id',
    ];

    /**
     * Get monthly events
     *
     * @param int $communityId
     * @param int $eventMonth
     * @param int $page
     * @return object
     */
    public static function getMonthlyEvents(int $communityId, int $eventMonth = 0, int $page = 0): object
    {
        $date = new \DateTime();

        $date->modify($eventMonth . ' month');
        $start = $date->format('Y-m') . '-01 00:00:00';

        $date->modify('+1 month');
        $end = $date->format('Y-m') . '-01 00:00:00';

        return self::where([
            'community_id' => $communityId,
            ['start_at', '>=', $start],
            ['start_at', '<', $end],
        ])->orderBy('start_at', 'asc')
            ->paginate(5, ['*'], 'page', $page);
    }
}
