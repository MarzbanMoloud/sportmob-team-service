<?php


namespace App\Services\EventStrategy;


use App\Exceptions\Projection\ProjectionException;
use App\Projections\Projector\TransferProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\Message;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class PlayerWasTransferred
 * @package App\Services\EventStrategy
 */
class PlayerWasTransferred implements EventInterface
{
	private TransferProjector $transferProjector;
	private SerializerInterface $serializer;

	/**
	 * PlayerWasTransferred constructor.
	 * @param TransferProjector $transferProjector
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TransferProjector $transferProjector,
		SerializerInterface $serializer
	) {
		$this->transferProjector = $transferProjector;
		$this->serializer = $serializer;
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	public function handle(Message $message): void
	{
		Event::handled($message, config('mediator-event.events.player_was_transferred'), __CLASS__);
		$this->transferProjector->applyPlayerWasTransferred($message);
	}
}