<?php


namespace App\Listeners\Traits;


use App\Models\ReadModels\Trophy;
use App\ValueObjects\Broker\Notification\Body as NotificationBody;
use App\ValueObjects\Broker\Notification\Headers as NotificationHeaders;
use App\ValueObjects\Broker\Notification\Message as NotificationMessage;
use DateTimeImmutable;

/**
 * Trait TeamBecameWinnerNotificationTrait
 * @package App\Listeners\Traits
 */
trait TeamBecameWinnerNotificationTrait
{
	/**
	 * @param Trophy $trophy
	 * @param string $event
	 */
	private function sendNotification(Trophy $trophy, string $event)
	{
		$notificationMessage = (new NotificationMessage())
			->setHeaders(
				(new NotificationHeaders())
					->setEvent($event)
					->setDate(new DateTimeImmutable())
					->setId($trophy->getSortKey())
			)
			->setBody(
				(new NotificationBody())
					->setId([
						'team' => $trophy->getTeamId(),
						'competition' => $trophy->getCompetitionId(),
					])
					->setMetadata([
						'competitionName' => $trophy->getCompetitionName(),
						'teamName' => $trophy->getTeamName()
					])
			);
		$this->broker->flushMessages()->addMessage(
			$event,
			$this->serializer->serialize($notificationMessage, 'json')
		)->produceMessage(config('broker.topics.notification'));
	}
}