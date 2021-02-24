<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\TeamProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\ValueObjects\Broker\Mediator\Message;
use App\ValueObjects\Broker\Mediator\MessageBody;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TeamWasCreated
 * @package App\Services\EventStrategy
 */
class TeamWasCreated implements EventInterface
{
	private TeamProjector $teamProjector;
	private LoggerInterface $logger;
	private SerializerInterface $serializer;
	private string $eventName;

	/**
	 * TeamWasCreated constructor.
	 * @param TeamProjector $teamProjector
	 * @param LoggerInterface $logger
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TeamProjector $teamProjector,
		LoggerInterface $logger,
		SerializerInterface $serializer
	) {
		$this->teamProjector = $teamProjector;
		$this->logger = $logger;
		$this->serializer = $serializer;
		$this->eventName = config('mediator-event.events.team_was_created');
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
		$this->teamProjector->applyTeamWasCreated($body);
	}
}