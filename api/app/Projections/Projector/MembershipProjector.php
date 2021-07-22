<?php


namespace App\Projections\Projector;


use App\Events\Projection\MembershipWasUpdatedProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Team\Traits\TeamTraits;
use App\Http\Services\Transfer\TransferService;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\Message;
use DateTime;
use DateTimeImmutable;
use Sentry\State\HubInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TransferProjector
 * @package App\Projections\Projector
 */
class MembershipProjector
{
	use TeamTraits;

	const TEAM_TYPE_CLUB = 'club';

	private TransferRepository $transferRepository;
	private TeamRepository $teamRepository;
	private TeamCacheServiceInterface $teamCacheService;
	private TransferService $transferService;
	private SerializerInterface $serializer;
	private TransferCacheServiceInterface $transferCacheService;
	private HubInterface $sentryHub;
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private string $eventName;

	/**
	 * TransferProjector constructor.
	 * @param TransferRepository $transferRepository
	 * @param TeamRepository $teamRepository
	 * @param TeamCacheServiceInterface $teamCacheService
	 * @param TransferService $transferService
	 * @param TransferCacheServiceInterface $transferCacheService
	 * @param SerializerInterface $serializer
	 * @param HubInterface $sentryHub
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 */
	public function __construct(
		TransferRepository $transferRepository,
		TeamRepository $teamRepository,
		TeamCacheServiceInterface $teamCacheService,
		TransferService $transferService,
		TransferCacheServiceInterface $transferCacheService,
		SerializerInterface $serializer,
		HubInterface $sentryHub,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService
	) {
		$this->transferRepository = $transferRepository;
		$this->teamRepository = $teamRepository;
		$this->teamCacheService = $teamCacheService;
		$this->transferService = $transferService;
		$this->serializer = $serializer;
		$this->transferCacheService = $transferCacheService;
		$this->sentryHub = $sentryHub;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	public function applyMembershipWasUpdated(Message $message)
	{
		$this->eventName = config('mediator-event.events.membership_was_updated');
		Event::processing($message, $this->eventName);

		$this->checkIdentifierValidation($message);

		$this->checkMetadataValidation($message);

		$this->transferHandle($message);

		//TODO:: create cache.

		Event::succeeded($message, $this->eventName);
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function checkIdentifierValidation(Message $message): void
	{
		$requiredFields = ['person'];
		foreach ($requiredFields as $fieldName) {
			if (empty($message->getBody()->getIdentifiers()[$fieldName])) {
				$validationMessage = sprintf("%s field is empty.", ucfirst($fieldName));
				Event::failed($message, $this->eventName, $validationMessage);
				throw new ProjectionException($validationMessage,
					ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
			}
		}
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function checkMetadataValidation(Message $message): void
	{
		$metadata = $message->getBody()->getMetadata();
		$requiredFields = ['membership', 'type'];
		foreach ($requiredFields as $fieldName) {
			if (empty($metadata[$fieldName])) {
				$validationMessage = sprintf("%s field is empty.", ucfirst($fieldName));
				Event::failed($message, $this->eventName, $validationMessage);
				throw new ProjectionException($validationMessage,
					ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
			}
		}
	}

	/**
	 * @param array $membership
	 * @return array
	 */
	private function getTeamsName(array $membership): array
	{
		$teamsName = [];
		foreach (['teamId', 'onLoanFrom'] as $field) {
			if ($membership[$field]) {
				try {
					$teamsName[$field] = $this->findTeam($membership[$field])->getName()->getOriginal();
				} catch (\Throwable $exception) {
				}
			}
		}
		return $teamsName;
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function transferHandle(Message $message): void
	{
		$body = $message->getBody();
		$metadata = $body->getMetadata();

		$transferModel = (new Transfer())
			->setPersonId($body->getIdentifiers()['person'])
			->setPersonType($metadata['type']);

		foreach ($metadata['membership'] as $membership) {
			if ($membership['teamType'] != self::TEAM_TYPE_CLUB) {
				continue;
			}
			$transferModel
				->setId($membership['id'])
				->setTeamId($membership['teamId'])
				->setOnLoanFromId($membership['onLoanFrom'] ?? Transfer::DEFAULT_VALUE)
				->setDateFrom($membership['dateFrom'] ? new DateTimeImmutable($membership['dateFrom']) : Transfer::getDateTimeImmutable())
				->setDateTo($membership['dateTo'] ? new DateTimeImmutable($membership['dateTo']) : null)
				->setCreatedAt(new DateTime());

			$teamsName = $this->getTeamsName($membership);

			if (isset($teamsName['teamId'])) {
				$transferModel->setTeamName($teamsName['teamId']);
			}
			if (isset($teamsName['onLoanFrom'])) {
				$transferModel->setOnLoanFromName($teamsName['onLoanFrom']);
			}

			$transferModel->prePersist();
			$this->persistTransfer($transferModel, $message);

			event(new MembershipWasUpdatedProjectorEvent($transferModel, $message));
		}
	}

	/**
	 * @param Transfer $transferModel
	 * @param Message $message
	 */
	private function persistTransfer(Transfer $transferModel, Message $message): void
	{
		try {
			$this->transferRepository->persist($transferModel);
		} catch (DynamoDBRepositoryException $exception) {
			Event::failed($message, $this->eventName, 'Failed to persist transfer.');
			$this->sentryHub->captureException($exception);
		}
	}
}