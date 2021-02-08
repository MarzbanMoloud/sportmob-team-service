<?php


namespace App\Listeners\Consumer;


use App\Events\Consumer\BrokerCommandEvent;
use App\Services\BrokerCommandStrategy\BrokerCommandContext;
use App\ValueObjects\Broker\CommandQuery\Message;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class BrokerCommandListener
 * @package App\Listeners\Consumer
 */
class BrokerCommandListener
{
    private SerializerInterface $serializer;
    private BrokerCommandContext $commandContext;

    /**
     * BrokerCommandListener constructor.
     * @param SerializerInterface $serializer
     * @param BrokerCommandContext $context
     */
    public function __construct(SerializerInterface $serializer, BrokerCommandContext $context)
    {
        $this->serializer     = $serializer;
        $this->commandContext = $context;
    }

    /**
     * @param BrokerCommandEvent $event
     */
    public function handle(BrokerCommandEvent $event)
    {
        $commandQuery = $this->serializer->deserialize($event->message->getBody(), Message::class, 'json');
        $this->commandContext->handle($commandQuery);
    }
}
