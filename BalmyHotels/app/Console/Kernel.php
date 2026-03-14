<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Her gece 06:00'da TripAdvisor puanlarını kaydet
        $schedule->command('tripadvisor:snapshot')->dailyAt('06:00');
        // Her gece 06:10'da Google puanlarını kaydet
        $schedule->command('google:snapshot')->dailyAt('06:10');        // Her sabah 08:00'de yarın son tarihi olan göreveleri hatırlat
        $schedule->command('tasks:send-reminders')->dailyAt('08:00');    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
