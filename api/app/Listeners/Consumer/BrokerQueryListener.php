<?php


namespace App\Listeners\Consumer;


use App\Events\Consumer\BrokerQueryEvent;
use App\Services\BrokerQueryStrategy\BrokerQueryContext;
use App\ValueObjects\Broker\CommandQuery\Message;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class BrokerQueryListener
 * @package App\Listeners\Consumer
 */
class BrokerQueryListener
{

    private SerializerInterface $serializer;
    private BrokerQueryContext $queryContext;

    /**
     * BrokerQueryListener constructor.
     * @param SerializerInterface $serializer
     * @param BrokerQueryContext $queryContext
     */
    public function __construct(SerializerInterface $serializer, BrokerQueryContext $queryContext)
    {
        $this->serializer   = $serializer;
        $this->queryContext = $queryContext;
    }

    /**
     * @param BrokerQueryEvent $event
     */
    public function handle(BrokerQueryEvent $event)
    {
        $commandQuery = $this->serializer->deserialize($event->message->getBody(), Message::class, 'json');
        $this->queryContext->handle($commandQuery);
    }
}
