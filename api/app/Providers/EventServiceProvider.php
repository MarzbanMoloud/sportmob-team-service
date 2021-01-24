<?php


namespace App\Providers;


use App\Events\Admin\TeamUpdatedEvent;
use App\Events\Projection\MatchWasCreatedProjectorEvent;
use App\Events\Projection\PlayerWasTransferredProjectorEvent;
use App\Events\Projection\TeamWasCreatedProjectorEvent;
use App\Events\Projection\TeamWasUpdatedProjectorEvent;
use App\Events\Projection\TrophyProjectorEvent;
use App\Listeners\Admin\TeamUpdatedListener;
use App\Listeners\Projection\MatchWasCreatedProjectorListener;
use App\Listeners\Projection\PlayerWasTransferredProjectorListener;
use App\Listeners\Projection\TeamCacheListener;
use App\Listeners\Projection\TrophyProjectorListener;
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

		/*----- Team Event Listener -----*/
		TeamWasCreatedProjectorEvent::class => [
			TeamCacheListener::class
		],

		TeamWasUpdatedProjectorEvent::class => [
			TeamCacheListener::class
		],

		PlayerWasTransferredProjectorEvent::class => [
			PlayerWasTransferredProjectorListener::class
		],

		TrophyProjectorEvent::class => [
			TrophyProjectorListener::class
		],

		MatchWasCreatedProjectorEvent::class => [
			MatchWasCreatedProjectorListener::class
		],

		TeamUpdatedEvent::class => [
			TeamUpdatedListener::class
		],

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
