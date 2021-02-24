<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\TrophyProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\ValueObjects\Broker\Mediator\Message;
use App\ValueObjects\Broker\Mediator\MessageBody;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TeamBecameWinner
 * @package App\Services\EventStrategy
 */
class TeamBecameWinner implements EventInterface
{
	private TrophyProjector $trophyProjector;
	private LoggerInterface $logger;
	private SerializerInterface $serializer;
	private string $eventName;

	/**
	 * TeamBecameWinner constructor.
	 * @param TrophyProjector $trophyProjector
	 * @param LoggerInterface $logger
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TrophyProjector $trophyProjector,
		LoggerInterface $logger,
		SerializerInterface $serializer
	) {
		$this->trophyProjector = $trophyProjector;
		$this->logger = $logger;
		$this->serializer = $serializer;
		$this->eventName = config('mediator-event.events.team_became_winner');
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
		$this->trophyProjector->applyTeamBecameWinner($body);
	}
}