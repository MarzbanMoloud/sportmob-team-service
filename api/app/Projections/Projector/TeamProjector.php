<?php


namespace App\Projections\Projector;


use App\Events\Projection\TeamWasCreatedProjectorEvent;
use App\Events\Projection\TeamWasUpdatedProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\TeamsMatch\TeamsMatchService;
use App\Http\Services\Transfer\TransferService;
use App\Http\Services\Trophy\TrophyService;
use App\Models\ReadModels\Team;
use App\Models\ReadModels\TeamsMatch;
use App\Models\ReadModels\Transfer;
use App\Models\ReadModels\Trophy;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TeamsMatchRepository;
use App\Models\Repositories\TransferRepository;
use App\Models\Repositories\TrophyRepository;
use App\Services\Cache\Interfaces\TeamsMatchCacheServiceInterface;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\Services\Cache\Interfaces\TrophyCacheServiceInterface;
use App\Services\Cache\TeamsMatchCacheService;
use App\ValueObjects\Broker\Mediator\MessageBody;
use App\ValueObjects\ReadModel\TeamName;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Sentry\State\HubInterface;


/**
 * Class TeamProjector
 * @package App\Projections\Projector
 */
class TeamProjector
{
	private TeamRepository $teamRepository;
	private LoggerInterface $logger;
	private SerializerInterface $serializer;
	private TeamsMatchRepository $teamsMatchRepository;
	private TeamsMatchCacheServiceInterface $teamsMatchCacheService;
	private TeamsMatchService $teamsMatchService;
	private HubInterface $sentryHub;
	private TransferRepository $transferRepository;
	private TransferCacheServiceInterface $transferCacheService;
	private TransferService $transferService;
	private TrophyRepository $trophyRepository;
	private TrophyCacheServiceInterface $trophyCacheService;
	private TrophyService $trophyService;
	private string $eventName;

	/**
	 * TeamProjector constructor.
	 * @param TeamRepository $teamRepository
	 * @param TeamsMatchRepository $teamsMatchRepository
	 * @param TeamsMatchService $teamsMatchService
	 * @param TeamsMatchCacheServiceInterface $teamsMatchCacheService
	 * @param TransferRepository $transferRepository
	 * @param TransferCacheServiceInterface $transferCacheService
	 * @param TransferService $transferService
	 * @param TrophyRepository $trophyRepository
	 * @param TrophyCacheServiceInterface $trophyCacheService
	 * @param TrophyService $trophyService
	 * @param LoggerInterface $logger
	 * @param SerializerInterface $serializer
	 * @param HubInterface $sentryHub
	 */
	public function __construct(
		TeamRepository $teamRepository,
		TeamsMatchRepository $teamsMatchRepository,
		TeamsMatchService $teamsMatchService,
		TeamsMatchCacheServiceInterface $teamsMatchCacheService,
		TransferRepository $transferRepository,
		TransferCacheServiceInterface $transferCacheService,
		TransferService $transferService,
		TrophyRepository $trophyRepository,
		TrophyCacheServiceInterface $trophyCacheService,
		TrophyService $trophyService,
		LoggerInterface $logger,
		SerializerInterface $serializer,
		HubInterface $sentryHub
	) {
		$this->teamRepository = $teamRepository;
		$this->logger = $logger;
		$this->serializer = $serializer;
		$this->teamsMatchRepository = $teamsMatchRepository;
		$this->sentryHub = $sentryHub;
		$this->teamsMatchService = $teamsMatchService;
		$this->teamsMatchCacheService = $teamsMatchCacheService;
		$this->transferRepository = $transferRepository;
		$this->transferCacheService = $transferCacheService;
		$this->transferService = $transferService;
		$this->trophyRepository = $trophyRepository;
		$this->trophyCacheService = $trophyCacheService;
		$this->trophyService = $trophyService;
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyTeamWasCreated(MessageBody $body): void
	{
		$this->eventName = config('mediator-event.events.team_was_created');
		$this->logger->alert(
			sprintf("%s handler in progress.", $this->eventName),
			$this->serializer->normalize($body, 'array')
		);
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		if (empty($identifier['team'])) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Team field is empty.'
				), $identifier
			);
			throw new ProjectionException('Team field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		$this->checkMetadataValidation($metadata);
		$this->checkItemExist($identifier['team']);
		$teamModel = $this->createTeamModel($identifier['team'], $metadata);
		$this->persistTeam($teamModel);
		event(new TeamWasCreatedProjectorEvent($teamModel));
		$this->logger->alert(
			sprintf("%s handler completed successfully.", $this->eventName),
			$this->serializer->normalize($teamModel, 'array')
		);
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyTeamWasUpdated(MessageBody $body): void
	{
		$this->eventName = config('mediator-event.events.team_was_updated');
		$this->logger->alert(
			sprintf("%s handler in progress.", $this->eventName),
			$this->serializer->normalize($body, 'array')
		);
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		if (empty($identifier['team'])) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Team field is empty.'
				), $identifier
			);
			throw new ProjectionException('Team field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		if (empty($metadata['fullName'])) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'FullName field is empty.'
				), $metadata
			);
			throw new ProjectionException('FullName field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		$teamModel = $this->checkItemNotExist($identifier['team'], $this->serializer->normalize($body, 'array'));
		$teamModel = $this->updateTeamModel($teamModel, $metadata);
		$this->persistTeam($teamModel);
		event(new TeamWasUpdatedProjectorEvent($teamModel));
		$this->updateOtherEntities($identifier['team'], $metadata);
		$this->logger->alert(
			sprintf("%s handler completed successfully.", $this->eventName),
			$this->serializer->normalize($teamModel, 'array')
		);
	}

	/**
	 * @param array $metadata
	 * @throws ProjectionException
	 */
	private function checkMetadataValidation(array $metadata): void
	{
		$requiredFields = [
			'fullName' => 'Full Name',
			'type' => 'Type',
			'country' => 'Country',
			'gender' => 'Gender'
		];
		foreach ($requiredFields as $fieldName => $prettyFieldName) {
			if (empty($metadata[$fieldName])) {
				$this->logger->alert(
					sprintf(
						"%s handler failed because of %s",
						$this->eventName,
						sprintf("%s field is empty.", $prettyFieldName)
					), $metadata
				);
				throw new ProjectionException(
					sprintf("%s field is empty.", $prettyFieldName),
					ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR
				);
			}
		}
		if (is_null($metadata['active'])) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Active field is empty.'
				), $metadata
			);
			throw new ProjectionException(
				sprintf("Active field is empty."),
				ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR
			);
		}
	}

	/**
	 * @param string $teamId
	 * @throws ProjectionException
	 */
	private function checkItemExist(string $teamId): void
	{
		$teamItem = $this->teamRepository->find(['id' => $teamId]);
		if ($teamItem) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					sprintf("Team already exist by following id: %s", $teamId)
				), $this->serializer->normalize($teamItem, 'array')
			);
			throw new ProjectionException(
				sprintf("Team already exist by following id: %s", $teamId),
				ResponseServiceInterface::STATUS_CODE_CONFLICT_ERROR
			);
		}
	}

	/**
	 * @param string $teamId
	 * @param array $body
	 * @return \App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryModelInterface
	 * @throws ProjectionException
	 */
	private function checkItemNotExist(string $teamId, array $body)
	{
		$teamItem = $this->teamRepository->find(['id' => $teamId]);
		if (!$teamItem) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					sprintf("Team already not exist by following id: %s", $teamId)
				), $body
			);
			throw new ProjectionException(
				sprintf("Team already not exist by following id: %s", $teamId),
				ResponseServiceInterface::STATUS_CODE_CONFLICT_ERROR
			);
		}
		return $teamItem;
	}

	/**
	 * @param string $teamId
	 * @param array $metadata
	 * @return Team
	 */
	private function createTeamModel(string $teamId, array $metadata): Team
	{
		return (new Team())
			->setId($teamId)
			->setGender($metadata['gender'])
			->setFounded($metadata['founded'] ?? '' )
			->setCountry($metadata['country'])
			->setCountryId($metadata['countryId'])
			->setCity($metadata['city'] ?? '')
			->setType($metadata['type'])
			->setName(
				(new TeamName())
					->setOriginal($metadata['fullName'])
					->setOfficial($metadata['officialName'])
					->setShort($metadata['shortName'])
			);
	}

	/**
	 * @param \App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryModelInterface $teamModel
	 * @param array $metadata
	 * @return mixed
	 */
	private function updateTeamModel(
		\App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryModelInterface $teamModel,
		array $metadata
	) {
		return $teamModel->setName(
			(new TeamName())
				->setOriginal($metadata['fullName'])
				->setOfficial($metadata['officialName'])
				->setShort($metadata['shortName'])
		);
	}

	/**
	 * @param string $team
	 * @param array $metadata
	 * @throws ProjectionException
	 */
	private function updateOtherEntities(string $team, array $metadata)
	{
		/**	TeamsMatch */
		foreach ([TeamsMatch::STATUS_UPCOMING, TeamsMatch::STATUS_FINISHED, TeamsMatch::STATUS_UNKNOWN] as $status) {
			$teamsMatch = $this->teamsMatchRepository->findTeamsMatchByTeamId($team, $status);
			if ($teamsMatch) {
				foreach ($teamsMatch as $item) {
					/** @var TeamsMatch $item */
					$item->setTeamName(
						(new TeamName())
							->setOriginal($metadata['fullName'])
							->setOfficial($metadata['officialName'])
							->setShort($metadata['shortName'])
					);
					$this->persistTeamsMatch($item);
				}
				try {
					$this->teamsMatchCacheService->forget(TeamsMatchCacheService::getTeamsMatchByTeamIdKey($team,
						$status));
					$this->teamsMatchService->getTeamsMatchInfo($team);
				} catch (\Exception $e) {
					$this->logger->alert(
						sprintf(
							"%s handler failed because of %s",
							$this->eventName,
							'Failed create or forget cache for teamsMatch.'
						), $this->serializer->normalize($item, 'array')
					);
				}
			}
			$teamsMatch = $this->teamsMatchRepository->findTeamsMatchByOpponentId($team);
			if ($teamsMatch) {
				foreach ($teamsMatch as $item) {
					/** @var TeamsMatch $item */
					$item->setOpponentName(
						(new TeamName())
							->setOriginal($metadata['fullName'])
							->setOfficial($metadata['officialName'])
							->setShort($metadata['shortName'])
					);
					$this->persistTeamsMatch($item);
				}
				try {
					$this->teamsMatchCacheService->forget(TeamsMatchCacheService::getTeamsMatchByTeamIdKey($team,
						$status));
					$this->teamsMatchService->getTeamsMatchInfo($team);
				} catch (\Exception $e) {
					$this->logger->alert(
						sprintf(
							"%s handler failed because of %s",
							$this->eventName,
							'Failed create or forget cache for teamsMatch.'
						), $this->serializer->normalize($item, 'array')
					);
				}
			}
		}
		/**	Transfer */
		$transfers = $this->transferRepository->findByTeamId($team);
		if ($transfers) {
			foreach ($transfers as $transfer) {
				/** @var Transfer $transfer */
				if ($transfer->getToTeamId() == $team) {
					$transfer->setToTeamName($metadata['fullName']);
				}
				if ($transfer->getFromTeamId() == $team) {
					$transfer->setFromTeamName($metadata['fullName']);
				}
				$this->persistTransfer($transfer);
			}
			try {
				$this->transferCacheService->forget('transfer_by_*');//per playerId and teamId
				$this->transferService->listByTeam($team);
			} catch (\Exception $e) {
				$this->logger->alert(
					sprintf(
						"%s handler failed because of %s",
						$this->eventName,
						'Failed create or forget cache for transfer.'
					), $this->serializer->normalize($transfer, 'array')
				);
			}
		}
		/**	Trophies */
		$trophies = $this->trophyRepository->findByTeamId($team);
		if ($trophies) {
			foreach ($trophies as $trophy) {
				/**	@var Trophy $trophy */
				$trophy->setTeamName($metadata['officialName']);
				$this->persistTrophy($trophy);
			}
			try {
				$this->trophyCacheService->forget('trophies_by_*');//per teamId and competitionId.
				$this->trophyService->getTrophiesByTeam($team);
			} catch (\Exception $e) {
				$this->logger->alert(
					sprintf(
						"%s handler failed because of %s",
						$this->eventName,
						'Failed create cache or forget for trophy.'
					), $this->serializer->normalize($trophy, 'array')
				);
			}
		}
	}

	/**
	 * @param Team $teamModel
	 * @throws ProjectionException
	 */
	private function persistTeam(Team $teamModel): void
	{
		try {
			$this->teamRepository->persist($teamModel);
		} catch (DynamoDBRepositoryException $exception) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Failed to persist team.'
				), $this->serializer->normalize($teamModel, 'array')
			);
			throw new ProjectionException('Failed to persist team.', $exception->getCode(), $exception);
		}
	}

	/**
	 * @param TeamsMatch $teamsMatchModel
	 */
	private function persistTeamsMatch(TeamsMatch $teamsMatchModel)
	{
		try {
			$this->teamsMatchRepository->persist($teamsMatchModel);
		} catch (DynamoDBRepositoryException $exception) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Failed to update teamsMatch.'
				), $this->serializer->normalize($teamsMatchModel, 'array')
			);
			$this->sentryHub->captureException($exception);
		}
	}

	/**
	 * @param Transfer $transferModel
	 */
	private function persistTransfer(Transfer $transferModel)
	{
		try {
			$this->transferRepository->persist($transferModel);
		} catch (DynamoDBRepositoryException $exception) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Failed to update transfer.'
				), $this->serializer->normalize($transferModel, 'array')
			);
			$this->sentryHub->captureException($exception);
		}
	}

	/**
	 * @param Trophy $trophyModel
	 */
	private function persistTrophy(Trophy $trophyModel)
	{
		try {
			$this->trophyRepository->persist($trophyModel);
		} catch (DynamoDBRepositoryException $exception) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Failed to update trophy.'
				), $this->serializer->normalize($trophyModel, 'array')
			);
			$this->sentryHub->captureException($exception);
		}
	}
}