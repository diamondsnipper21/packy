<?php

namespace App\Console\Commands\Migrate;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateLevelDefaultValue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:update-level-default-value';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update default value of level column.';

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
        DB::table('community_members')
            ->where('level', '0')
            ->update([
                'level' => '1'
            ]);
    }
}
