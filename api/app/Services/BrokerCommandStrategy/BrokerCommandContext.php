<?php


namespace App\Services\BrokerCommandStrategy;


use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandEventInterface;
use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandStrategyInterface;
use App\ValueObjects\Broker\CommandQuery\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class BrokerCommandContext
 * @package App\Services\BrokerCommandStrategy
 */
class BrokerCommandContext implements BrokerCommandStrategyInterface
{
	private LoggerInterface $logger;
	private SerializerInterface $serializer;

	/**
	 * BrokerCommandContext constructor.
	 * @param LoggerInterface $logger
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		LoggerInterface $logger,
		SerializerInterface $serializer
	) {
		$this->logger = $logger;
		$this->serializer = $serializer;
	}

    /**
     * @param Message $message
     * @return mixed|void
     */
    public function handle(Message $message): void
    {
		$this->logger->alert(
			sprintf("Answer %s by %s received.", $message->getHeaders()->getKey(), $message->getHeaders()->getSource()),
			$this->serializer->normalize($message, 'array')
		);
        foreach (app()->tagged(BrokerCommandEventInterface::TAG_NAME) as $event) {
            if ($event->support($message->getHeaders())) {
                $event->handle($message);
                return;
            }
        }
		$this->logger->alert(
			sprintf("Answer %s by %s rejected (lack of ownership).", $message->getHeaders()->getKey(), $message->getHeaders()->getSource()),
			$this->serializer->normalize($message, 'array')
		);
    }
}
