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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {      
        $schedule->command('email:surveys')->daily();
        $schedule->command('email:rental_period_reminders')->daily();       
        $schedule->command('queue:work --stop-when-empty --queue=password_resets')->everyMinute();
        $schedule->command('queue:work --stop-when-empty --queue=geocode')->everyMinute();
        $schedule->command('queue:work --stop-when-empty --queue=chat')->everyMinute();
        $schedule->command('queue:work --stop-when-empty --queue=emails')->everyFiveMinutes();
       // $schedule->command('queue:work --stop-when-empty --queue=embeds')->everyFiveMinutes();
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
