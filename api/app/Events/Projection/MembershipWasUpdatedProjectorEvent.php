<?php


namespace App\Events\Projection;


use App\Events\Event;
use App\Models\ReadModels\Transfer;
use App\ValueObjects\Broker\Mediator\Message;


/**
 * Class MembershipWasUpdatedProjectorEvent
 * @package App\Events\Projection
 */
class MembershipWasUpdatedProjectorEvent extends Event
{
	public Transfer $transfer;
	public Message $mediatorMessage;

	/**
	 * MembershipWasUpdatedProjectorEvent constructor.
	 * @param Transfer $transfer
	 * @param Message $mediatorMessage
	 */
	public function __construct(Transfer $transfer, Message $mediatorMessage)
	{
		$this->transfer = $transfer;
		$this->mediatorMessage = $mediatorMessage;
	}
}