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
        Commands\Inspire::class,
        Commands\DropTables::class,
        Commands\SetAllPasswords::class,
        Commands\Munge::class,
        Commands\LoadReport::class,
        Commands\CreateImportedTable::class,
        Commands\GrantRole::class,
        Commands\RevokeRole::class,
        Commands\ListRoles::class,
        Commands\ListDocuments::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }
}
