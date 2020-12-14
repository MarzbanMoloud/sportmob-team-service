<?php


namespace App\Listeners\Consumer;


use App\Events\Consumer\BrokerQueryEvent;
use App\Services\BrokerQueryStrategy\BrokerQueryContext;
use App\ValueObjects\Broker\CommandQuery\Message;


/**
 * Class BrokerQueryListener
 * @package App\Listeners\Consumer
 */
class BrokerQueryListener
{
    /**
     * @param BrokerQueryEvent $event
     */
    public function handle(BrokerQueryEvent $event)
    {
        $commandQuery = app('Serializer')->deserialize($event->message->getBody(), Message::class, 'json');
        (new BrokerQueryContext())->handle($commandQuery);
    }
}
