<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\TransferProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\ValueObjects\Broker\Mediator\Message;
use App\ValueObjects\Broker\Mediator\MessageBody;


/**
 * Class PlayerWasTransferred
 * @package App\Services\EventStrategy
 */
class PlayerWasTransferred implements EventInterface
{
	private TransferProjector $transferProjector;

	/**
	 * PlayerWasTransferred constructor.
	 * @param TransferProjector $transferProjector
	 */
	public function __construct(TransferProjector $transferProjector)
	{
		$this->transferProjector = $transferProjector;
	}

	/**
	 * @param Message $message
	 * @return bool
	 */
	public function support(Message $message): bool
	{
		return $message->getHeaders()->getEvent() == config('mediator-event.events.player_was_transferred');
	}

	/**
	 * @param MessageBody $body
	 * @throws \App\Exceptions\Projection\ProjectionException
	 */
	public function handle(MessageBody $body): void
	{
		$this->transferProjector->applyPlayerWasTransferred($body);
	}
}