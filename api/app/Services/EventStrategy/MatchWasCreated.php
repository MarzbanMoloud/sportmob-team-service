<?php


namespace App\Services\EventStrategy;


use App\Exceptions\Projection\ProjectionException;
use App\Projections\Projector\MatchProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\Message;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class MatchWasCreated
 * @package App\Services\EventStrategy
 */
class MatchWasCreated implements EventInterface
{
	private MatchProjector $matchProjector;
	private SerializerInterface $serializer;

	/**
	 * MatchWasCreated constructor.
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
	 * @param Message $message
	 * @throws ProjectionException
	 */
	public function handle(Message $message): void
	{
		Event::handled($message, config('mediator-event.events.match_was_created'), __CLASS__);
		$this->matchProjector->applyMatchWasCreated($message);
	}
}