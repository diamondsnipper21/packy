<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('command:check-events')->dailyAt('07:00');
        $schedule->command('command:image-optimize')->cron('40 11 11 3 *');
        $schedule->command('command:monitor-scheduled-post')->cron('*/10 * * * *');
        $schedule->command('command:monitor-member-point')->dailyAt('01:00');
        $schedule->command('command:cancel-subscription-handler')->cron('40 11 11 3 *');
        $schedule->command('command:webhook-events')->everyMinute();

        $schedule->command('command:members-subscription-renewal-reminder')->dailyAt('07:15');
        $schedule->command('command:members-subscription-cancels')->dailyAt('23:50');

        $schedule->command('command:community-plans-check-status')->dailyAt('00:01');
        $schedule->command('command:community-plans-overdue-reminder')->dailyAt('07:00');
        $schedule->command('command:community-plans-renewal-reminder')->dailyAt('07:15');
        $schedule->command('command:community-plans-renewal-trial-reminder')->dailyAt('07:30');

        $this->scheduleSendPopular($schedule);
        $this->scheduleSendUnread($schedule);
    }

    /**
     * @param Schedule $schedule
     * @param string $command
     * @return void
     */
    private function scheduleSendPopular(Schedule $schedule, string $command = 'command:send-popular'): void
    {
        $schedule->command($command, ['daily'])->cron('0 18 * * *')->withoutOverlapping();
        $schedule->command($command, ['weekly'])->weeklyOn(1, '8:00')->withoutOverlapping();
        $schedule->command($command, ['14days'])->twiceMonthly(1, 15, '8:00')->withoutOverlapping();
        $schedule->command($command, ['monthly'])->monthlyOn(1, '8:00')->withoutOverlapping();
    }

    /**
     * @param Schedule $schedule
     * @param string $command
     * @return void
     */
    private function scheduleSendUnread(Schedule $schedule, string $command = 'command:send-unread'): void
    {
        $schedule->command($command, ['hourly'])->hourly()->withoutOverlapping();
        $schedule->command($command, ['3-hours'])->everyThreeHours()->withoutOverlapping();
        $schedule->command($command, ['6-hours'])->everySixHours()->withoutOverlapping();
        $schedule->command($command, ['12-hours'])->twiceDaily(1, 13)->withoutOverlapping();
        $schedule->command($command, ['daily'])->cron('0 18 * * *')->withoutOverlapping();
        $schedule->command($command, ['weekly'])->weeklyOn(1, '8:00')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        $this->load(__DIR__.'/Commands/Migrate');

        require base_path('routes/console.php');
    }
}
