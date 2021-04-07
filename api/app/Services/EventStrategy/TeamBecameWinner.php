<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\TrophyProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\MessageBody;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TeamBecameWinner
 * @package App\Services\EventStrategy
 */
class TeamBecameWinner implements EventInterface
{
	private TrophyProjector $trophyProjector;
	private SerializerInterface $serializer;

	/**
	 * TeamBecameWinner constructor.
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
	 * @param MessageBody $body
	 * @throws \App\Exceptions\Projection\ProjectionException
	 */
	public function handle(MessageBody $body): void
	{
		Event::handled($body, config('mediator-event.events.team_became_winner'), __CLASS__);
		$this->trophyProjector->applyTeamBecameWinner($body);
	}
}