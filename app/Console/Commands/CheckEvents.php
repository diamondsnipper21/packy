<?php

namespace App\Console\Commands;

use App\Enum\LangEnum;
use App\Mail\EventReminderMail;
use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\CommunityMember;
use App\Models\CommunityMemberSetting;
use App\Services\LoggerService;
use App\Services\ClassroomService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $communities = Community::orderBy('created_at', 'asc')->get();
        if (!$communities) {
            return;
        }

        foreach ($communities as $community)
        {
            $communityId = $community->id;
            $communityOwner = $community->user;

            $replyTo = $communityOwner->email;
            if ($communityOwner->id === 1) {
                $replyTo = config('mail.support');
            }

            $date = new \DateTime();
            $date->modify('+1 day');
            $start = $date->format('Y-m-d') . ' 00:00:00';
            $end = $date->format('Y-m-d') . ' 23:59:59';

            $events = CommunityEvent::where(['community_id' => $communityId, ['start_at', '>=', $start], ['start_at', '<', $end]])->orderBy('start_at', 'asc')->get();
            if (!$events) {
                continue;
            }

            $receivers = [];
            $notificationSettings = CommunityMemberSetting::where(['community_id' => $communityId, 'event_reminder' => 1])->get();
            if ($notificationSettings) {
                foreach ($notificationSettings as $notificationSetting) {
                    $memberId = $notificationSetting->member_id;

                    $member = CommunityMember::find($memberId);
                    if ($member) {
                        $receivers[$memberId] = $member;
                    }
                }
            }

            foreach ($events as $event) {
                foreach ($receivers as $receiver) {
                    $receiverUser = $receiver->user;
                    if (!empty($receiverUser) && ClassroomService::checkAccessPermissionForObject($event->access_type, $event->access_value, $receiver->id, $receiver->role)) {
                        $email = $receiverUser->email;
                        $language = $receiverUser->language ?? LangEnum::LANG_ENGLISH;

                        try {
                            Mail::to($email)->send(new EventReminderMail(
                                $event->title,
                                $event->start_at,
                                $event->timezone,
                                $language,
                                $replyTo,
                                $event->description,
                                $event->link
                            ));
                        } catch (\Exception $e) {
                            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                            \Log::error(['EventReminder Exception', $e->getMessage()]);
                        }
                    }
                }
            }
        }
    }
}
