<?php


namespace App\Services\EventStrategy;


use App\Exceptions\Projection\ProjectionException;
use App\Projections\Projector\MembershipProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\Message;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class MembershipWasUpdated
 * @package App\Services\EventStrategy
 */
class MembershipWasUpdated implements EventInterface
{
	private MembershipProjector $membershipProjector;
	private SerializerInterface $serializer;

	/**
	 * MembershipWasUpdated constructor.
	 * @param MembershipProjector $membershipProjector
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		MembershipProjector $membershipProjector,
		SerializerInterface $serializer
	) {
		$this->membershipProjector = $membershipProjector;
		$this->serializer = $serializer;
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	public function handle(Message $message): void
	{
		Event::handled($message, config('mediator-event.events.membership_was_updated'), __CLASS__);
		$this->membershipProjector->applyMembershipWasUpdated($message);
	}
}