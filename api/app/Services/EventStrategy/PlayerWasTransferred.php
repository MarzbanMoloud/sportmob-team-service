<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\TransferProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\ValueObjects\Broker\Mediator\Message;
use App\ValueObjects\Broker\Mediator\MessageBody;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class PlayerWasTransferred
 * @package App\Services\EventStrategy
 */
class PlayerWasTransferred implements EventInterface
{
	private TransferProjector $transferProjector;
	private LoggerInterface $logger;
	private SerializerInterface $serializer;
	private string $eventName;

	/**
	 * PlayerWasTransferred constructor.
	 * @param TransferProjector $transferProjector
	 * @param LoggerInterface $logger
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TransferProjector $transferProjector,
		LoggerInterface $logger,
		SerializerInterface $serializer
	) {
		$this->transferProjector = $transferProjector;
		$this->logger = $logger;
		$this->serializer = $serializer;
		$this->eventName = config('mediator-event.events.player_was_transferred');
	}

	/**
	 * @param Message $message
	 * @return bool
	 */
	public function support(Message $message): bool
	{
		return $message->getHeaders()->getEvent() == $this->eventName;
	}

	/**
	 * @param MessageBody $body
	 * @throws \App\Exceptions\Projection\ProjectionException
	 */
	public function handle(MessageBody $body): void
	{
		$this->logger->alert(
			sprintf("Event %s will handle by %s.", $this->eventName, __CLASS__),
			$this->serializer->normalize($body, 'array')
		);
		$this->transferProjector->applyPlayerWasTransferred($body);
	}
}