<?php

namespace App\Console\Commands\Migrate;

use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateNotificationSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migrate-notification-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate notification summary data after column is added';

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
        // From post
        DB::table('notifications as n')
            ->join('community_posts as p', 'p.id', '=', 'n.object_id')
            ->whereIn('n.object_type', [Notification::OT_LIKE_TO_POST, Notification::OT_DISLIKE_TO_POST, Notification::OT_REPLY_TO_POST])
            ->update([
                'n.owner_id' => DB::raw("`p`.`member_id`"),
                'n.summary' => DB::raw("SUBSTRING(`p`.`title`, 1, 250)"),
            ]);

        // From comments
        DB::table('notifications as n')
            ->join('community_post_comments as p', 'p.id', '=', 'n.object_id')
            ->whereIn('n.object_type', [Notification::OT_LIKE_TO_COMMENT, Notification::OT_DISLIKE_TO_COMMENT, Notification::OT_REPLY_TO_COMMENT])
            ->update([
                'n.owner_id' => DB::raw("`p`.`member_id`"),
                'n.summary' => DB::raw("SUBSTRING(`p`.`content`, 1, 250)"),
            ]);

        // From community
        DB::table('notifications as n')
            ->join('community as c', 'c.id', '=', 'n.community_id')
            ->whereIn('n.object_type', [Notification::OT_DECLINED_TO_JOIN, Notification::OT_APPROVED_TO_JOIN])
            ->update([
                'n.owner_id' => DB::raw("`n`.`object_id`"),
                'n.summary' => DB::raw("SUBSTRING(`c`.`summary_description`, 1, 250)"),
            ]);        
    }
}
