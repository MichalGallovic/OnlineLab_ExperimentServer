<?php

namespace App\Console;

use App\Device;
use App\Console\Commands\PingDevices;
use App\Console\Commands\ResetAppServer;
use App\Console\Commands\AddDeviceCommand;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\FlushDatabaseQueue;
use App\Console\Commands\ClearExperimentLogs;
use App\Console\Commands\RefreshCompiledClassmap;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    //@Todo remove ClearExperimentLogs - very dangerous :D
    //@Todo remove ResetAppServer - even more dangerous :D
    protected $commands = [
        ClearExperimentLogs::class,
        ResetAppServer::class,
        PingDevices::class,
        RefreshCompiledClassmap::class,
        AddDeviceCommand::class,
        FlushDatabaseQueue::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('server:devices:ping')->everyMinute();
    }
}
