<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('schedule:timestamp')->everyMinute();

        $schedule->command('backup:database')->daily()->runInBackground();
        $schedule->command('exchanger:update')->daily()->runInBackground();
        $schedule->command('geoip:update')->daily()->runInBackground();

        $schedule->command('wallet-addresses:consolidate')->everyThirtyMinutes()->runInBackground();
        $schedule->command('commerce-transactions:cancel')->everyMinute()->withoutOverlapping()->runInBackground();
        $schedule->command('transfer-records:update')->everyMinute()->withoutOverlapping()->runInBackground();

        $schedule->command('payment-transactions:update')->everyThirtyMinutes()->runInBackground();

        $schedule->command('peer-offers:update-visible')->everyMinute()->withoutOverlapping()->runInBackground();
        $schedule->command('peer-offers:update-hidden')->everyMinute()->withoutOverlapping()->runInBackground();
        $schedule->command('peer-trades:cancel')->everyMinute()->withoutOverlapping()->runInBackground();

        $schedule->command('stakes:update-pending')->everyMinute()->withoutOverlapping()->runInBackground();

        $schedule->command('users:update-presence')->everyMinute()->withoutOverlapping()->runInBackground();
        $schedule->command('statistics:update')->everyMinute()->withoutOverlapping()->runInBackground();

        $schedule->command('websockets:clean')->daily()->runInBackground();
        $schedule->command('queue:prune-failed')->daily()->runInBackground();
        $schedule->command('system-logs:clear')->daily()->runInBackground();

        if ($this->app->environment('local')) {
            $schedule->command('telescope:prune')->daily();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
