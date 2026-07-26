<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Storage;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // Periodic backup (Spatie + Backpack BackupManager → disk "backups")
        // #region agent log
        $backupLog = storage_path('logs/backup-schedule.log');
        // #endregion
        $schedule->command('backup:clean')
            ->daily()->at('04:00')
            ->appendOutputTo($backupLog);
        $schedule->command('backup:run')
            ->daily()->at('05:00')
            ->appendOutputTo($backupLog);
        $schedule->command('backup:monitor')
            ->daily()->at('06:00')
            ->appendOutputTo($backupLog);

        $schedule->command('telescope:prune')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
