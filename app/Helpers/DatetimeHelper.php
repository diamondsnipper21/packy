<?php

namespace App\Helpers;

use App\Models\CommunityMemberSetting;
use Carbon\Carbon;

class DatetimeHelper
{
    /**
     * @param string $period
     * @return string
     */
    public static function getTimeLimit(string $period): string
    {
        $timeLimit = '';

        switch ($period) {
            case CommunityMemberSetting::POPULAR_INTERVAL_OPT_DAILY:
                $timeLimit = date('Y-m-d', strtotime('-1 days')) . ' 00:00:00';
                break;
            case CommunityMemberSetting::POPULAR_INTERVAL_OPT_WEEKLY:
                $timeLimit = date('Y-m-d', strtotime('-7 days')) . ' 00:00:00';
                break;
            case CommunityMemberSetting::POPULAR_INTERVAL_OPT_14DAYS:
                $timeLimit = date('Y-m-d', strtotime('-14 days')) . ' 00:00:00';
                break;
            case CommunityMemberSetting::POPULAR_INTERVAL_OPT_MONTHLY:
                $timeLimit = date('Y-m-d', strtotime('-1 months')) . ' 00:00:00';
                break;

            case CommunityMemberSetting::UNREAD_INTERVAL_OPT_HOURLY:
                $timeLimit = date('Y-m-d H:i:s', strtotime('-1 hour'));
                break;
            case CommunityMemberSetting::UNREAD_INTERVAL_OPT_3_HOURS:
                $timeLimit = date("Y-m-d H:i:s", strtotime("-3 hours"));
                break;
            case CommunityMemberSetting::UNREAD_INTERVAL_OPT_6_HOURS:
                $timeLimit = date("Y-m-d H:i:s", strtotime("-6 hours"));
                break;
            case CommunityMemberSetting::UNREAD_INTERVAL_OPT_12_HOURS:
                $timeLimit = date("Y-m-d H:i:s", strtotime("-12 hours"));
                break;
            case CommunityMemberSetting::UNREAD_INTERVAL_OPT_2_DAY:
                $timeLimit = date('Y-m-d', strtotime('-2 days')) . ' 00:00:00';
                break;
        }

        return $timeLimit;
    }

    /**
     * @param string $timestamp
     * @return string
     */
    public static function timestampToDate(string $timestamp): string
    {
        return Carbon::createFromTimestamp($timestamp)->format('Y-m-d H:i:s');
    }
}
