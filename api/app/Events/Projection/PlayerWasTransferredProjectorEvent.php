<?php


namespace App\Events\Projection;


use App\Events\Event;
use App\Models\ReadModels\Transfer;


/**
 * Class PlayerWasTransferredProjectorEvent
 * @package App\Events\Projection
 */
class PlayerWasTransferredProjectorEvent extends Event
{
	public Transfer $transfer;

	/**
	 * PlayerWasTransferredProjectorEvent constructor.
	 * @param Transfer $transfer
	 */
	public function __construct(Transfer $transfer)
	{
		$this->transfer = $transfer;
	}
}