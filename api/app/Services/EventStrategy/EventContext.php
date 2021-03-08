<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/28/2020
 * Time: 10:59 AM
 */

namespace App\Services\EventStrategy;


use App\Services\EventStrategy\Interfaces\EventInterface;
use App\Services\EventStrategy\Interfaces\EventStrategyInterface;
use App\ValueObjects\Broker\Mediator\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class EventContext
 * @package App\Services\EventStrategy
 */
class EventContext implements EventStrategyInterface
{
	private LoggerInterface $logger;
	private SerializerInterface $serializer;

	/**
	 * EventContext constructor.
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
     * @param $message
     * @return mixed|void
     */
    public function handle(Message $message)
    {
		$this->logger->alert(
			sprintf("Event %s received.", $message->getHeaders()->getEvent()),
			$this->serializer->normalize($message, 'array')
		);
        foreach (app()->tagged(EventInterface::TAG_NAME) as $event) {
            if ($event->support($message)) {
                $event->handle($message->getBody());
                return;
            }
        }
		$this->logger->alert(
			sprintf("Event %s rejected (lack of ownership).", $message->getHeaders()->getEvent()),
			$this->serializer->normalize($message, 'array')
		);
    }
}
