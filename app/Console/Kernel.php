<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\BukaPortalCommand::class,
        \App\Console\Commands\TutupPortalCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('portal:buka')->everyMinute()->timezone('Asia/Jakarta')->between('8:00', '08:30');
        $schedule->command('portal:tutup')->everyMinute()->timezone('Asia/Jakarta')->between('10:00', '10:30');
    }
}
