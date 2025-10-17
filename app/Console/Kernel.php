<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ProcessRecordingSchedules::class,
        Commands\CheckFavoriteProgramsBroadcast::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(new \App\Routine\InsertRadioProgram($schedule))->dailyAt('5:00');
        $schedule->call(new \App\Routine\DeleteDuplicateRecords($schedule))->dailyAt('5:00');

        // 録音予約処理を毎分実行
        $schedule->command('recording:process-schedules')->everyMinute();

        // お気に入り番組の放送チェックを5分ごとに実行
        $schedule->command('favorites:check-broadcast')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        // コマンドは$commandsプロパティで登録

        require base_path('routes/console.php');
    }
}
