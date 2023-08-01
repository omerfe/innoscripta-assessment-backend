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
        $schedule->command('app:fetch-news-api-articles')->daily()->at('10:00')->timezone('Europe/Istanbul');
        $schedule->command('app:fetch-n-y-times-api-articles')->daily()->at('10:00')->timezone('Europe/Istanbul');
        $schedule->command('app:fetch-the-guardian-api-articles')->daily()->at('10:00')->timezone('Europe/Istanbul');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
