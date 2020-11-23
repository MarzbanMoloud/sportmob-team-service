<?php


namespace App\Providers;


use App\Events\Consumer\{BrokerCommandEvent, BrokerMediatorEvent, BrokerQueryEvent};
use App\Listeners\Consumer\{BrokerCommandListener, BrokerMediatorListener, BrokerQueryListener};
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;


/**
 * Class EventServiceProvider
 * @package App\Providers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

        /*----- Broker - Mediator -----*/
        BrokerMediatorEvent::class => [
            BrokerMediatorListener::class
        ],

        /*----- Broker - Query -----*/
        BrokerQueryEvent::class => [
            BrokerQueryListener::class
        ],

        /*----- Broker - Command -----*/
        BrokerCommandEvent::class => [
            BrokerCommandListener::class
        ]
    ];

    public function register()
    {
        /*----- Call boot method for run listener after event raised. -----*/
        if (app()->runningUnitTests()) {
            $this->boot();
        }
    }
}
