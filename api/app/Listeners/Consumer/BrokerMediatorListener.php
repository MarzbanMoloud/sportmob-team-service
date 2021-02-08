<?php


namespace App\Listeners\Consumer;


use App\Events\Consumer\BrokerMediatorEvent;
use App\Services\EventStrategy\EventContext;
use App\ValueObjects\Broker\Mediator\Message;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class BrokerMediatorListener
 * @package App\Listeners\Consumer
 */
class BrokerMediatorListener
{
    private SerializerInterface $serializer;
    private EventContext $eventContext;

    /**
     * BrokerMediatorListener constructor.
     * @param SerializerInterface $serializer
     * @param EventContext $eventContext
     */
    public function __construct(SerializerInterface $serializer, EventContext $eventContext)
    {
        $this->serializer   = $serializer;
        $this->eventContext = $eventContext;
    }

    /**
     * @param BrokerMediatorEvent $event
     */
    public function handle(BrokerMediatorEvent $event)
    {
        $messageValueObject = $this->serializer->deserialize($event->message->getBody(), Message::class, 'json');
        $this->eventContext->handle($messageValueObject);
    }
}
