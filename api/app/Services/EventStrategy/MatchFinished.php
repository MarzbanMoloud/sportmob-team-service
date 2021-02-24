<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\MatchProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\ValueObjects\Broker\Mediator\Message;
use App\ValueObjects\Broker\Mediator\MessageBody;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class MatchFinished
 * @package App\Services\EventStrategy
 */
class MatchFinished implements EventInterface
{
	private MatchProjector $matchProjector;
	private LoggerInterface $logger;
	private SerializerInterface $serializer;
	private string $eventName;

	/**
	 * MatchFinished constructor.
	 * @param MatchProjector $matchProjector
	 * @param LoggerInterface $logger
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		MatchProjector $matchProjector,
		LoggerInterface $logger,
		SerializerInterface $serializer
	) {
		$this->matchProjector = $matchProjector;
		$this->logger = $logger;
		$this->serializer = $serializer;
		$this->eventName = config('mediator-event.events.match_finished');
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
		$this->matchProjector->applyMatchFinished($body);
	}
}