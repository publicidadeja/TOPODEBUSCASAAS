<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\TestGoogleImport::class,
        Commands\UpdateBusinessAnalytics::class
    ];
    

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('business:analyze')
        ->dailyAt('02:00')
        ->withoutOverlapping();
        $schedule->command('analytics:update')
                 ->dailyAt('00:01')
                 ->appendOutputTo(storage_path('logs/analytics.log'));
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    

    
}