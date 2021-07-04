<?php


namespace App\Events\Projection;


use App\Events\Event;
use App\Models\ReadModels\Transfer;
use App\ValueObjects\Broker\Mediator\Message;


/**
 * Class PlayerWasTransferredProjectorEvent
 * @package App\Events\Projection
 */
class PlayerWasTransferredProjectorEvent extends Event
{
	public Transfer $transfer;
	public Message $mediatorMessage;

	/**
	 * PlayerWasTransferredProjectorEvent constructor.
	 * @param Transfer $transfer
	 * @param Message $mediatorMessage
	 */
	public function __construct(Transfer $transfer, Message $mediatorMessage)
	{
		$this->transfer = $transfer;
		$this->mediatorMessage = $mediatorMessage;
	}
}