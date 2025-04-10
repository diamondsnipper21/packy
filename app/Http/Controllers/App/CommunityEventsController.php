<?php

namespace App\Http\Controllers\App;

use App\Models\Community;
use App\Models\CommunityEvent;
use App\Services\EventService;
use App\Services\LoggerService;
use Illuminate\Http\Request;
use Spatie\CalendarLinks\Link;

class CommunityEventsController extends AppController
{
    private EventService $eventService;

    /**
     * @param EventService $eventService
     */
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }
    
    /**
     * @param Request $request
     * @return array
     */
    public function get(Request $request): array
    {
        $community = Community::where(['id' => $request->communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        return [
            'success' => true,
            'communityEvents' => CommunityEvent::where(['community_id' => $community->id])->orderBy('created_at', 'desc')->get(),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getOne(Request $request): array
    {
        $id = $request->id ?? 0;
        $communityId = $request->communityId ?? 0;

        $community = Community::where(['id' => $communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        $communityEvent = CommunityEvent::where(['id' => $id, 'community_id' => $communityId])->first();
        if (!$communityEvent) {
            return ['success' => false, 'message' => __('Community event not found')];
        }

        $title = $communityEvent->title;
        $description = $communityEvent->description ?? '';
        $eventLink = $communityEvent->link ?? '';
        if ($eventLink) {
            $description .= "\n\n" . $eventLink;
        }
        $timezone = $communityEvent->timezone ?? '';

        $from = \DateTime::createFromFormat('Y-m-d H:i:s', $communityEvent->start_at);

        $endAt = $communityEvent->end_at;
        $duration = $communityEvent->duration;
        if ($endAt && str_starts_with($endAt, 'on_')) {
            $to = \DateTime::createFromFormat('Y-m-d H:i:s', str_replace('on_', '', $endAt));
        } elseif ($duration) {
            $durationMin = floatval($duration) * 60;
            $to = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime('+' . $durationMin . ' minutes', strtotime($communityEvent->start_at))));
        }

        $link = Link::create($title, $from, $to)
            ->description($description)
            ->address($timezone);

        $communityEvent->links = [
            'google' => $link->google(),
            'apple' => $link->ics(),
            'outlook' => $link->webOutlook(),
            'outlookcom' => $link->webOffice(),
            'yahoo' => $link->yahoo(),
        ];

        return [
            'success' => true,
            'communityEvent' => $communityEvent,
        ];
    }
    
    /**
     * @param Request $request
     * @return array
     */
    public function saveEvent(Request $request): array
    {
        $id = $request->id ?? 0;
        $communityId = $request->community_id ?? 0;
        $title = $request->title ?? '';
        $description = $request->description ?? '';
        $changeToAll = $request->change_to_all ?? 0;
        $startAt = $request->start_at ?? '';
        $endAt = $request->end_at ?? '';
        $duration = $request->duration ?? '';
        $repeat = $request->repeat ?? 0;
        $repeatEvery = $request->repeat_every ?? '';
        $repeatOn = $request->repeat_on ?? '';
        $timezone = $request->timezone ?? '';
        $media = $request->media ?? '';
        $link = $request->link ?? '';
        $type = $request->type ?? '';
        $location = $request->location ?? '';
        $accessType = $request->access_type ?? 'all';
        $accessValue = $request->access_value ?? '';
        $level = $request->level ?? 1;
        $eventMonth = $request->eventMonth ?? 0; // @todo - unused ?

        if ($accessType === 'all') {
            $accessValue = '';
        }

        if ($id == 0) {
            $event = new CommunityEvent();
        } else {
            $event = CommunityEvent::find($id);
            if (empty($event)) {
                return ['success' => false, 'message' => __('Community Event not found')];
            }

            $originEndAt = $event->end_at;
            $originRepeatEvery = $event->repeat_every;
            $originRepeatOn = $event->repeat_on;
        }

        try {
            $event->community_id = $communityId;
            $event->title = $title;
            $event->description = $description;
            $event->start_at = $startAt;
            $event->duration = $duration;
            $event->timezone = $timezone;
            $event->media = $media;
            $event->link = $link;
            $event->type = $type;
            $event->location = $location;
            $event->access_type = $accessType;
            $event->access_value = $accessValue;
            $event->level = $level;

            if ($repeat) {
                $event->repeat_every = $repeatEvery;
                $event->repeat_on = $repeatOn;
                $event->end_at = $endAt;
            }

            $event->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        $eventId = $event->id;
        if ($event->origin_id > 0) {
            $eventId = $event->origin_id;
        }

        if ($id == 0) {
            if ($repeat) {
                $this->eventService->cloneRepeatEvents($eventId);
            }
        } else {
            if ($changeToAll && $repeat) {
                if ($originEndAt !== $endAt || $originRepeatEvery !== $repeatEvery || $originRepeatOn !== $repeatOn) {
                    $this->eventService->cloneRepeatEvents($eventId);
                } else {
                    $this->eventService->updateAllEvents($eventId);
                }
            }
        }

        return [
            'success' => true,
            'events' => CommunityEvent::where(['community_id' => $communityId])->orderBy('start_at', 'asc')->get(),
        ];
    }
    
    /**
     * @param Request $request
     * @return array
     */
    public function deleteEvent(Request $request): array
    {
        $id = $request->id ?? 0;
        $communityId = $request->community_id ?? 0;
        $changeToAll = $request->change_to_all ?? 0;

        if ($changeToAll) {
            $this->eventService->deleteAllEvents($id);
        } else {
            try {
                CommunityEvent::where(['community_id' => $communityId, 'id' => $id])->delete();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return [
            'success' => true,
            'events' => CommunityEvent::where(['community_id' => $communityId])->orderBy('start_at', 'asc')->get(),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getMonthlyEvents(Request $request): array
    {
        $communityId = $request->communityId ?? 0;
        $eventMonth = $request->eventMonth ?? 0;
        $page = $request->page ?? 0;

        $community = Community::where(['id' => $communityId])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        return [
            'success' => true,
            'monthlyEvents' => CommunityEvent::getMonthlyEvents($community->id, $eventMonth, $page),
        ];
    }
}