<?php


namespace App\Listeners\Consumer;


use App\Events\Consumer\BrokerMediatorEvent;
use App\Services\EventStrategy\EventContext;
use App\ValueObjects\Broker\Mediator\Message;


/**
 * Class BrokerMediatorListener
 * @package App\Listeners\Consumer
 */
class BrokerMediatorListener
{
    /**
     * @param BrokerMediatorEvent $event
     */
    public function handle(BrokerMediatorEvent $event)
    {
        $messageValueObject = app('Serializer')->deserialize($event->message->getBody(), Message::class, 'json');
        (new EventContext())->handle($messageValueObject);
    }
}
