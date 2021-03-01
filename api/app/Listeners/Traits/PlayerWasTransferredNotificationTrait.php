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
		//TODO:: Log for notification
		$notificationMessage = (new NotificationMessage())
			->setHeaders(
				(new NotificationHeaders())
					->setEvent($event)
					->setDate(new DateTimeImmutable())
					->setId($transfer->getPlayerId())
			)
			->setBody(
				(new NotificationBody())
					->setId([
						'team' => [
							$transfer->getFromTeamId(),
							$transfer->getToTeamId()
						],
						'player' => $transfer->getPlayerId(),
						'owner' => $transfer->getToTeamName()
					])
					->setMetadata([
						'player_name' => $transfer->getPlayerName(),
						'old_team_name' => $transfer->getFromTeamName(),
						'team_name' => $transfer->getToTeamName(),
					])
			);
		$this->broker->flushMessages()->addMessage(
			$event,
			$this->serializer->serialize($notificationMessage, 'json')
		)->produceMessage(config('broker.topics.notification'));
	}
}