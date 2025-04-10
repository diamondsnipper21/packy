<?php

namespace App\Services;

use App\Models\CommunityEvent;

/**
 * Class EventService
 *
 * @package App\Services
 */
class EventService
{
    /**
     * Check cloned event exist or not
     *
     * @param CommunityEvent $event
     * @param int $startAtTimestamp
     * @return bool
     */
    private function clonedEventExist(CommunityEvent $event, int $startAtTimestamp): bool
    {
        $exist = false;
        $eventId = $event->id;

        $cloned = CommunityEvent::query()
            ->where('start_at', gmdate('Y-m-d H:i:s', $startAtTimestamp))
            ->where(function ($subQuery) use ($eventId) {
                return $subQuery
                    ->where(['id' => $eventId])
                    ->orWhere(['origin_id' => $eventId]);
            })->first();

        if ($cloned) {
            $exist = true;
        }

        return $exist;
    }

    /**
     * Clone one event
     *
     * @param CommunityEvent $event
     * @param int $startAtTimestamp
     * @return array
     */
    private function cloneEvent(CommunityEvent $event, int $startAtTimestamp): array
    {
        $endAt = $event->end_at;
        $eventId = $event->id;

        if (str_starts_with($endAt, 'after')) {
            $endAtAfter = (int)str_replace('after_', '', $endAt);
            if ($endAtAfter > 0) {
                $events = CommunityEvent::where(['origin_id' => $eventId])->get();
                if (count($events) >= $endAtAfter) {
                    return ['success' => false, 'message' => __('Event overflow')];
                }
            }
        }

        try {
            $newEvent = $event->replicate();
            $newEvent->start_at = gmdate('Y-m-d H:i:s', $startAtTimestamp);
            $newEvent->origin_id = $event->id;
            $newEvent->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true];
    }

    /**
     * Clone events
     *
     * @param CommunityEvent $event
     * @param array $startAtTimestampLists
     * @return void
     */
    private function cloneEvents(CommunityEvent $event, array $startAtTimestampLists): void
    {
        $repeatEvery = $event->repeat_every;
        if (!empty($repeatEvery) && count($startAtTimestampLists) > 0) {
            foreach ($startAtTimestampLists as $startAtTimestamp) {
                if (!$this->clonedEventExist($event, $startAtTimestamp)) {
                    $this->cloneEvent($event, $startAtTimestamp);
                }
            }
        }
    }

    /**
     * Get repeat start at timestamp
     *
     * @param CommunityEvent $event
     * @param int $startAtTimestamp
     * @return array
     */
    private function startAtTimestampLists(CommunityEvent $event, int $startAtTimestamp): array
    {
        $startAtTimestampLists = [];

        $repeatEvery = $event->repeat_every;
        $endAt = $event->end_at;

        if (!empty($repeatEvery) && $startAtTimestamp > 0) {
            $arrayRepeatEvery = explode('_', $repeatEvery);
            if (count($arrayRepeatEvery) === 2) {
                $repeatValPrefix = (int)$arrayRepeatEvery[0];
                $repeatValSuffix = $arrayRepeatEvery[1];

                $intervalTimestamp = 0;
                if ($repeatValSuffix === 'day') {
                    $intervalTimestamp = 24 * 60 * 60;
                } elseif ($repeatValSuffix === 'week') {
                    $intervalTimestamp = 7 * 24 * 60 * 60;
                } elseif ($repeatValSuffix === 'month') {
                    $intervalTimestamp = 30 * 24 * 60 * 60;
                } elseif ($repeatValSuffix === 'year') {
                    $intervalTimestamp = 365 * 24 * 60 * 60;
                }

                if ($intervalTimestamp > 0) {
                    $nextTimestamp = $startAtTimestamp + $repeatValPrefix * $intervalTimestamp;

                    if (str_starts_with($endAt, 'on')) {
                        $endAtOn = str_replace('on_', '', $endAt);
                        $endAtTimestamp = strtotime($endAtOn);

                        if ($endAtTimestamp > 0) {
                            while ($nextTimestamp < $endAtTimestamp) {
                                $startAtTimestampLists[] = $nextTimestamp;
                                $nextTimestamp += $repeatValPrefix * $intervalTimestamp;
                            }
                        }
                    } elseif (str_starts_with($endAt, 'after')) {
                        $endAtAfter = (int)str_replace('after_', '', $endAt);

                        if ($endAtAfter > 0) {
                            $count = 0;
                            while ($count < $endAtAfter) {
                                $startAtTimestampLists[] = $nextTimestamp;
                                $count++;
                                $nextTimestamp += $repeatValPrefix * $intervalTimestamp;
                            }
                        }
                    }
                }
            }
        }

        return array_unique($startAtTimestampLists);
    }

    /**
     * @todo - move this to DatetimeHelper - ugly function
     *
     * Get new timestamp from weekday
     *
     * @param int $startAtTimestamp
     * @param string $weekday
     * @return int
     */
    private function getWeekDayTimeStamp(int $startAtTimestamp, string $weekday): int
    {
        $newTimestamp = 0;
        $possibleWeekDays = ['mon', 'tue', 'wed', 'thurs', 'fri', 'sat', 'sun'];

        if (!empty($weekday) && in_array($weekday, $possibleWeekDays)) {
            $startAtStartTimestamp = strtotime(gmdate('Y-m-d', $startAtTimestamp));
            $diffTimestamp = $startAtTimestamp - $startAtStartTimestamp;
            $weekDiffDay = (int)array_search($weekday, $possibleWeekDays) + 1;
            $date = new \DateTime(gmdate('Y-m-d H:i:s', $startAtTimestamp));
            $week = $date->format('W');

            $firstDayOfYear = date('Y-m-d', strtotime('first day of January ' . date('Y', $startAtTimestamp))) . ' 00:00:00';

            // calculate day of week of NEW year's day
            $dayOfWeekOfFirstDay = date('w', strtotime($firstDayOfYear));
            if ($dayOfWeekOfFirstDay === 0) {
                $dayOfWeekOfFirstDay = 7;
            }

            $d = $weekDiffDay + ($week - 1) * 7 - ($dayOfWeekOfFirstDay - 1);
            $weekdayStartTimestamp = strtotime($firstDayOfYear) + ($d - 1) * 24 * 3600;
            $weekdayEndTimestamp = strtotime($firstDayOfYear) + $d * 24 * 3600;
            $newTimestamp = $weekdayStartTimestamp + $diffTimestamp;
        }

        return $newTimestamp;
    }

    /**
     * @todo - move this to DatetimeHelper - ugly function
     *
     * Get new timestamp from weekday
     *
     * @param int $startAtTimestamp
     * @param int $monthday
     * @return int
     */
    private function getMonthDayTimeStamp(int $startAtTimestamp, int $monthday): int
    {
        $startAtDate = gmdate('Y-m-d', $startAtTimestamp);
        $startAtStartTimestamp = strtotime($startAtDate);

        $diffTimestamp = $startAtTimestamp - $startAtStartTimestamp;

        $startAtDateArray = explode('-', $startAtDate);

        $monthDate = (string)$monthday;
        if ($monthday < 10) {
            $monthDate = '0' . (string)$monthday;
        }

        $startAtDateArray[2] = $monthDate;

        $monthDayStr = implode('-', $startAtDateArray);

        $monthDayStartTimestamp = strtotime($monthDayStr);
        $monthDayTimestamp = $monthDayStartTimestamp + $diffTimestamp;

        return $monthDayTimestamp;
    }

    /**
     * Clone repeat events
     * 
     * @param int $eventId
     * @return array
     */
    public function cloneRepeatEvents(int $eventId): array
    {
        $event = CommunityEvent::find($eventId);
        if (!$event) {
            return ['success' => false, 'message' => __('Community event not found')];
        }

        // Delete old cloned repeat events
        CommunityEvent::where(['origin_id' => $eventId])->delete();

        $repeatEvery = $event->repeat_every;
        $repeatOn = $event->repeat_on;
        $startAt = $event->start_at;
        $endAt = $event->end_at;

        $startAtTimestamp = 0;
        if (!empty($startAt)) {
            $startAtTimestamp = strtotime($startAt);
        }

        if (!empty($repeatEvery) && !empty($repeatOn)) {
            $arrayRepeatOn = explode('_', $repeatOn);
            $repeatOnPrefix = $arrayRepeatOn[0];
            $repeatOnSuffix = $arrayRepeatOn[1];

            $startAtTimestampLists = [];
            if ($repeatOnPrefix === 'day') {
                $startAtTimestampLists = $this->startAtTimestampLists($event, $startAtTimestamp);
            } elseif ($repeatOnPrefix === 'week') {
                if (!empty($repeatOnSuffix)) {
                    $repeatOnSuffixArray = explode('-', $repeatOnSuffix);
                    foreach ($repeatOnSuffixArray as $weekday) {
                        $newTimestamp = $this->getWeekDayTimeStamp($startAtTimestamp, $weekday);
                        if ($newTimestamp > 0) {
                            $weekStartAtTimestampLists = $this->startAtTimestampLists($event, $newTimestamp);
                            if (!empty($weekStartAtTimestampLists)) {
                                foreach ($weekStartAtTimestampLists as $weekStartAtTimestamp) {
                                    $startAtTimestampLists[] = $weekStartAtTimestamp;
                                }
                            }
                        }
                    }

                    $startAtTimestampLists = array_unique($startAtTimestampLists);
                    asort($startAtTimestampLists);

                    if (str_starts_with($endAt, 'after')) {
                        $endAtAfter = (int)str_replace('after_', '', $endAt);
                        if ($endAtAfter > 0) {
                            $startAtTimestampLists = array_slice($startAtTimestampLists, 0, $endAtAfter);
                        }
                    }
                }
            } elseif ($repeatOnPrefix === 'month') {
                if (!empty($repeatOnSuffix)) {
                    $newTimestamp = $this->getMonthDayTimeStamp($startAtTimestamp, (int)$repeatOnSuffix);
                    if ($newTimestamp > 0) {
                        $startAtTimestampLists = $this->startAtTimestampLists($event, $newTimestamp);
                    }
                }
            } elseif ($repeatOnPrefix === 'year') {
                if (!empty($repeatOnSuffix)) {
                    $startAtTimestampLists = $this->startAtTimestampLists($event, strtotime($repeatOnSuffix));
                }
            }

            if (!empty($startAtTimestampLists)) {
                $this->cloneEvents($event, $startAtTimestampLists);
            }
        }

        return ['success' => true];
    }

    /**
     * Update all recurring events
     * 
     * @param int $eventId
     * @return array
     */
    public function updateAllEvents(int $eventId): array
    {
        $event = CommunityEvent::find($eventId);
        if (!$event) {
            return ['success' => false, 'message' => __('Community event not found')];
        }

        $id = $eventId;
        if ($event->origin_id > 0) {
            $id = $event->origin_id;
        }

        // @todo - move to repository
        $query = CommunityEvent::query();
        $query->where(function ($subQuery) use ($id) {
            return $subQuery
                ->where(['id' => $id])
                ->orWhere(['origin_id' => $id]);
        });
        $events = $query->get();

        if (!empty($events)) {
            foreach ($events as $item) {
                try {
                    $item->title = $event->title;
                    $item->description = $event->description;
                    $item->duration = $event->duration;
                    $item->timezone = $event->timezone;
                    $item->media = $event->media;
                    $item->link = $event->link;
                    $item->type = $event->type;
                    $item->location = $event->location;
                    $item->repeat_every = $event->repeat_every;
                    $item->repeat_on = $event->repeat_on;
                    $item->end_at = $event->end_at;
                    $item->access_type = $event->access_type;
                    $item->access_value = $event->access_value;
                    $item->level = $event->level;
                    $item->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }
        }

        return ['success' => true];
    }

    /**
     * Delete all recurring events
     * 
     * @param int $eventId
     * @return array
     */
    public function deleteAllEvents(int $eventId): array
    {
        $event = CommunityEvent::find($eventId);
        if (!$event) {
            return ['success' => false, 'message' => __('Community event not found')];
        }

        $id = $eventId;
        if ($event->origin_id > 0) {
            $id = $event->origin_id;
        }

        try {
            CommunityEvent::query()->where(function ($subQuery) use ($id) {
                return $subQuery
                    ->where(['id' => $id])
                    ->orWhere(['origin_id' => $id]);
            })->delete();
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true];
    }
}
