<?php

namespace App\Console\Commands\Migrate;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Community;
use App\Models\CommunityClassroom;
use App\Models\CommunityClassroomLesson;
use App\Models\CommunityClassroomSet;

class UpdateCommunityItemsOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:update-community-items-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update community items order - classroom, set, lesson';

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
     *
     * @return void
     */
    public function handle(): void
    {
        $communities = Community::all(['id']);
        foreach ($communities as $community) {
            DB::table('community_classrooms as t1')
                ->joinSub(function ($query) use ($community) {
                    $query->select([DB::raw('ROW_NUMBER() OVER (ORDER BY `order`) AS row_num'), 'id'])
                        ->from('community_classrooms')
                        ->where('community_id', $community->id)
                        ->orderBy('order');
                }, 't2', 't1.id', '=', 't2.id')
                ->where('community_id', $community->id)
                ->update(['t1.order' => DB::raw('CAST(t2.row_num AS UNSIGNED)')]);

            $rooms = CommunityClassroom::where(['community_id' => $community->id])->get();
            foreach ($rooms as $room) {
                $items = [];
                $lessons = CommunityClassroomLesson::where('classroom_id', $room->id)
                    ->whereNull('set_id')
                    ->get();
                foreach ($lessons as $lesson) {
                    $items[] = [
                        'type' => 'lesson',
                        'id' => $lesson->id,
                        'order' => $lesson->order,
                    ];
                }

                // Update order of lesson under set
                $sets = CommunityClassroomSet::where(['classroom_id' => $room->id])->get();
                foreach ($sets as $set) {
                    DB::table('community_classrooms_lessons as t1')
                        ->joinSub(function ($query) use ($room, $set) {
                            $query->select([DB::raw('ROW_NUMBER() OVER (ORDER BY `order`) AS row_num'), 'id'])
                                ->from('community_classrooms_lessons')
                                ->where('classroom_id', $room->id)
                                ->where('set_id', $set->id)
                                ->orderBy('order');
                        }, 't2', 't1.id', '=', 't2.id')
                        ->where('classroom_id', $room->id)
                        ->where('set_id', $set->id)
                        ->update(['t1.order' => DB::raw('CAST(t2.row_num AS UNSIGNED)')]);

                    $items[] = [
                        'type' => 'set',
                        'id' => $set->id,
                        'order' => $set->order,
                    ];
                }

                // Update order sets and lessons under room
                usort($items, fn($a, $b) => $a['order'] > $b['order'] ? 1 : -1);
                foreach ($items as $key => $value) {
                    if ($value['type'] == 'set') {
                        CommunityClassroomSet::where(['id' => $value['id']])->update(['order' => $key + 1]);
                    } else {
                        CommunityClassroomLesson::where(['id' => $value['id']])->update(['order' => $key + 1]);
                    }
                }
                sleep(1);
            }
        }
    }
}
