<?php


namespace App\Console;


use App\Console\Commands\Broker\RemoveTopicsAndQueuesCommand;
use App\Console\Commands\Consumer\CommandEventsConsumerCommand;
use App\Console\Commands\Consumer\QueryEventsConsumerCommand;
use App\Console\Commands\Consumer\MediatorEventsConsumerCommand;
use App\Console\Commands\DynamoDB\MakeTableCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;


/**
 * Class Kernel
 * @package App\Console
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        MediatorEventsConsumerCommand::class,
        QueryEventsConsumerCommand::class,
        CommandEventsConsumerCommand::class,
        MakeTableCommand::class,
        RemoveTopicsAndQueuesCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
