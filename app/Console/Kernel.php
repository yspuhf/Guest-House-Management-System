<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Scheduled task to prune expired temporary blocks or log daily occupancy
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}

