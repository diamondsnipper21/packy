<?php

use App\Helpers\DatetimeHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCommunityPlansDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_plans', function (Blueprint $table) {
            $table->dateTime('current_period_start_date')->nullable()->after('current_period_start');
            $table->dateTime('current_period_end_date')->nullable()->after('current_period_end');
            $table->dateTime('trial_start_date')->nullable()->after('trial_start');
            $table->dateTime('trial_end_date')->nullable()->after('trial_end');
        });

        $plans = DB::table('community_plans')->get();
        foreach ($plans as $plan) {
            DB::table('community_plans')
                ->where('id', $plan->id)
                ->update([
                    'current_period_start_date' => $plan->current_period_start ? DatetimeHelper::timestampToDate($plan->current_period_start) : NULL,
                    'current_period_end_date' => $plan->current_period_end ? DatetimeHelper::timestampToDate($plan->current_period_end) : NULL,
                    'trial_start_date' => $plan->trial_start ? DatetimeHelper::timestampToDate($plan->trial_start) : NULL,
                    'trial_end_date' => $plan->trial_end ? DatetimeHelper::timestampToDate($plan->trial_end) : NULL,
                ]);
        }

        Schema::table('community_plans', function (Blueprint $table) {
            $table->dropColumn('current_period_start');
            $table->dropColumn('current_period_end');
            $table->dropColumn('trial_start');
            $table->dropColumn('trial_end');
        });

        Schema::table('community_plans', function (Blueprint $table) {
            $table->renameColumn('current_period_start_date', 'current_period_start');
            $table->renameColumn('current_period_end_date', 'current_period_end');
            $table->renameColumn('trial_start_date', 'trial_start');
            $table->renameColumn('trial_end_date', 'trial_end');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
