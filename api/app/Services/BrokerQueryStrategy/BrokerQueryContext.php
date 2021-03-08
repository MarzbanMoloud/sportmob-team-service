<?php


namespace App\Services\BrokerQueryStrategy;


use App\Services\BrokerQueryStrategy\Interfaces\BrokerQueryEventInterface;
use App\Services\BrokerQueryStrategy\Interfaces\BrokerQueryStrategyInterface;
use App\ValueObjects\Broker\CommandQuery\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class BrokerQueryContext
 * @package App\Services\BrokerQueryStrategy
 */
class BrokerQueryContext implements BrokerQueryStrategyInterface
{
	private LoggerInterface $logger;
	private SerializerInterface $serializer;

	/**
	 * BrokerQueryContext constructor.
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
    public function handle(Message $message)
    {
		$this->logger->alert(
			sprintf("Question %s by %s received.", $message->getHeaders()->getKey(), $message->getHeaders()->getSource()),
			$this->serializer->normalize($message, 'array')
		);

        foreach (app()->tagged(BrokerQueryEventInterface::TAG_NAME) as $event) {
            if ($event->support($message)) {
                $event->handle($message);
                return;
            }
        }
		$this->logger->alert(
			sprintf("Question %s by %s rejected (lack of ownership).", $message->getHeaders()->getKey(), $message->getHeaders()->getSource()),
			$this->serializer->normalize($message, 'array')
		);
    }
}
