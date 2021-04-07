<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\MatchProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\MessageBody;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class MatchFinished
 * @package App\Services\EventStrategy
 */
class MatchFinished implements EventInterface
{
	private MatchProjector $matchProjector;
	private SerializerInterface $serializer;

	/**
	 * MatchFinished constructor.
	 * @param MatchProjector $matchProjector
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		MatchProjector $matchProjector,
		SerializerInterface $serializer
	) {
		$this->matchProjector = $matchProjector;
		$this->serializer = $serializer;
	}

	/**
	 * @param MessageBody $body
	 * @throws \App\Exceptions\Projection\ProjectionException
	 */
	public function handle(MessageBody $body): void
	{
		Event::handled($body, config('mediator-event.events.match_finished'), __CLASS__);
		$this->matchProjector->applyMatchFinished($body);
	}
}