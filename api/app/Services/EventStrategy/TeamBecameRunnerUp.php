<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\TrophyProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\Message;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TeamBecameRunnerUp
 * @package App\Services\EventStrategy
 */
class TeamBecameRunnerUp implements EventInterface
{
	private TrophyProjector $trophyProjector;
	private SerializerInterface $serializer;

	/**
	 * TeamBecameRunnerUp constructor.
	 * @param TrophyProjector $trophyProjector
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TrophyProjector $trophyProjector,
		SerializerInterface $serializer
	) {
		$this->trophyProjector = $trophyProjector;
		$this->serializer = $serializer;
	}

	/**
	 * @param Message $message
	 * @throws \App\Exceptions\Projection\ProjectionException
	 */
	public function handle(Message $message): void
	{
		Event::handled($message, config('mediator-event.events.team_became_runner_up'), __CLASS__);
		$this->trophyProjector->applyTeamBecameRunnerUp($message);
	}
}