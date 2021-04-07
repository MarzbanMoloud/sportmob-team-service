<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\TransferProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\MessageBody;
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
	 * @param MessageBody $body
	 * @throws \App\Exceptions\Projection\ProjectionException
	 */
	public function handle(MessageBody $body): void
	{
		Event::handled($body, config('mediator-event.events.player_was_transferred'), __CLASS__);
		$this->transferProjector->applyPlayerWasTransferred($body);
	}
}