<?php


namespace App\Listeners\Traits;


use App\Models\ReadModels\Transfer;
use App\ValueObjects\Broker\Notification\Body as NotificationBody;
use App\ValueObjects\Broker\Notification\Headers as NotificationHeaders;
use App\ValueObjects\Broker\Notification\Message as NotificationMessage;
use DateTimeImmutable;


/**
 * Trait PlayerWasTransferredNotificationTrait
 * @package App\Listeners\Traits
 */
trait PlayerWasTransferredNotificationTrait
{
	/**
	 * @param Transfer $transfer
	 * @param string $event
	 */
	private function sendNotification(Transfer $transfer, string $event)
	{
		//TODO:: Contract!
		$notificationMessage = (new NotificationMessage())
			->setHeaders(
				(new NotificationHeaders())
					->setEvent($event)
					->setDate(new DateTimeImmutable())
			)
			->setBody(
				(new NotificationBody())
					->setId([
						'from' => $transfer->getFromTeamId(),
						'to' => $transfer->getToTeamId(),
						'player' => $transfer->getPlayerId()
					])
					->setMetadata([
						'playerPosition' => $transfer->getPlayerPosition(),
						'playerName' => $transfer->getPlayerName(),
						'fromTeamName' => $transfer->getFromTeamName(),
						'toTeamName' => $transfer->getToTeamName(),
						'startDate' => $transfer->getStartDate(),
						'endDate' => $transfer->getEndDate(),
						'season' => $transfer->getSeason(),
					])
			);
		$this->broker->flushMessages()->addMessage(
			$event,
			$this->serializer->serialize($notificationMessage, 'json')
		)->produceMessage(config('broker.topics.notification'));
	}
}