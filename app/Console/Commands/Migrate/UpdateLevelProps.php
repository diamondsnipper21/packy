<?php

namespace App\Console\Commands\Migrate;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateLevelProps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migrate-update-level-props';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the level property of elements';

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
        DB::table('community_classrooms_lessons')
            ->where('access_type', 'only_level')
            ->update([
                'access_type' => 'all',
                'level' => DB::raw("`access_value`"),
            ]);

        DB::table('community_classrooms')
            ->where('access_type', 'only_level')
            ->update([
                'access_type' => 'all',
                'level' => DB::raw("`access_value`"),
            ]);

        DB::table('community_classrooms_sets')
            ->where('access_type', 'only_level')
            ->update([
                'access_type' => 'all',
                'level' => DB::raw("`access_value`"),
            ]);
    }
}
