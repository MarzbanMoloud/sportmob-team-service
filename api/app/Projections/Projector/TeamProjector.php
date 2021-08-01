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
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\Message;
use App\ValueObjects\ReadModel\TeamName;
use DateTime;
use Symfony\Component\Serializer\SerializerInterface;
use Sentry\State\HubInterface;


/**
 * Class TeamProjector
 * @package App\Projections\Projector
 */
class TeamProjector
{
	private TeamRepository $teamRepository;
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
		SerializerInterface $serializer,
		HubInterface $sentryHub
	) {
		$this->teamRepository = $teamRepository;
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
	 * @param Message $message
	 * @throws ProjectionException
	 */
	public function applyTeamWasCreated(Message $message): void
	{
		$this->eventName = config('mediator-event.events.team_was_created');

		Event::processing($message, $this->eventName);

		$body = $message->getBody();
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();

		if (empty($identifier['team'])) {
			$validationMessage = 'Team field is empty.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}

		$this->checkMetadataValidation($message);

		$teamModel = $this->createTeamModel($identifier['team'], $metadata);

		$this->persistTeam($teamModel, $message);

		event(new TeamWasCreatedProjectorEvent($teamModel));

		Event::succeeded($message, $this->eventName);
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	public function applyTeamWasUpdated(Message $message): void
	{
		$this->eventName = config('mediator-event.events.team_was_updated');

		Event::processing($message, $this->eventName);

		$body = $message->getBody();
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();

		if (empty($identifier['team'])) {
			$validationMessage = 'Team field is empty.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}

		if (empty($metadata['fullName'])) {
			$validationMessage = 'FullName field is empty.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}

		$teamModel = $this->checkItemNotExist($identifier['team'], $message);

		$teamModel = $this->updateTeamModel($teamModel, $metadata);

		$this->persistTeam($teamModel, $message);

		event(new TeamWasUpdatedProjectorEvent($teamModel));

		$this->updateOtherEntities($identifier['team'], $message);

		Event::succeeded($message, $this->eventName);
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function checkMetadataValidation(Message $message): void
	{
		$metadata = $message->getBody()->getMetadata();
		$requiredFields = ['fullName', 'type', 'country', 'gender'];
		foreach ($requiredFields as $fieldName) {
			if (empty($metadata[$fieldName])) {
				$validationMessage = sprintf("%s field is empty.", ucfirst($fieldName));
				Event::failed($message, $this->eventName, $validationMessage);
				throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
			}
		}
		if (is_null($metadata['active'])) {
			$validationMessage = 'Active field is empty.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
	}

	/**
	 * @param string $teamId
	 * @param Message $message
	 * @return \App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryModelInterface
	 * @throws ProjectionException
	 */
	private function checkItemNotExist(string $teamId, Message $message)
	{
		$teamItem = $this->teamRepository->find(['id' => $teamId]);
		if (!$teamItem) {
			$validationMessage = sprintf("Team does not exist by following id: %s", $teamId);
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_CONFLICT_ERROR);
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
			)
			->setCreatedAt(new DateTime());
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
	 * @param Message $message
	 */
	private function updateOtherEntities(string $team, Message $message)
	{
		$metadata = $message->getBody()->getMetadata();
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
					$this->persistTeamsMatch($item, $message);
				}
				try {
					$this->teamsMatchCacheService->forget(TeamsMatchCacheService::getTeamsMatchByTeamIdKey($team,
						$status));
					$this->teamsMatchService->getTeamsMatchInfo($team);
				} catch (\Exception $e) {
					Event::failed($message, $this->eventName, 'Failed create or forget cache for teamsMatch.');
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
					$this->persistTeamsMatch($item, $message);
				}
				try {
					$this->teamsMatchCacheService->forget(TeamsMatchCacheService::getTeamsMatchByTeamIdKey($team,
						$status));
					$this->teamsMatchService->getTeamsMatchInfo($team);
				} catch (\Exception $e) {
					Event::failed($message, $this->eventName, 'Failed create or forget cache for teamsMatch.');
				}
			}
		}
		/**	Transfer */
		$transfers = array_merge(
			$this->transferRepository->findAllByTeamIdAndSeason(Transfer::ATTR_TO_TEAM_ID, $team) ?? [],
			$this->transferRepository->findAllByTeamIdAndSeason(Transfer::ATTR_FROM_TEAM_ID, $team) ?? []
		);
		if ($transfers) {
			foreach ($transfers as $transfer) {
				/** @var Transfer $transfer */
				if ($transfer->getToTeamId() == $team) {
					$transfer->setToTeamName($metadata['fullName']);
				}
				if ($transfer->getFromTeamId() == $team) {
					$transfer->setFromTeamName($metadata['fullName']);
				}
				$this->persistTransfer($transfer, $message);
			}
			try {
				$this->transferCacheService->forget('transfer_by_*');//per playerId and teamId
				$this->transferService->listByTeam($team);
			} catch (\Exception $e) {
				Event::failed($message, $this->eventName, 'Failed create or forget cache for transfer.');
			}
		}
		/**	Trophies */
		$trophies = $this->trophyRepository->findByTeamId($team);
		if ($trophies) {
			foreach ($trophies as $trophy) {
				/**	@var Trophy $trophy */
				$trophy->setTeamName($metadata['officialName']);
				$this->persistTrophy($trophy, $message);
			}
			try {
				$this->trophyCacheService->forget('trophies_by_*');//per teamId and competitionId.
				$this->trophyService->getTrophiesByTeam($team);
			} catch (\Exception $e) {
				Event::failed($message, $this->eventName, 'Failed create cache or forget for trophy.');
			}
		}
	}

	/**
	 * @param Team $teamModel
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function persistTeam(Team $teamModel, Message $message): void
	{
		try {
			$this->teamRepository->persist($teamModel);
		} catch (DynamoDBRepositoryException $exception) {
			$validationMessage = 'Failed to persist team.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, $exception->getCode(), $exception);
		}
	}

	/**
	 * @param TeamsMatch $teamsMatchModel
	 * @param Message $message
	 */
	private function persistTeamsMatch(TeamsMatch $teamsMatchModel, Message $message)
	{
		try {
			$this->teamsMatchRepository->persist($teamsMatchModel);
		} catch (DynamoDBRepositoryException $exception) {
			$validationMessage = 'Failed to update teamsMatch.';
			Event::failed($message, $this->eventName, $validationMessage);
			$this->sentryHub->captureException($exception);
		}
	}

	/**
	 * @param Transfer $transferModel
	 * @param Message $message
	 */
	private function persistTransfer(Transfer $transferModel, Message $message)
	{
		try {
			$this->transferRepository->persist($transferModel);
		} catch (DynamoDBRepositoryException $exception) {
			Event::failed($message, $this->eventName, 'Failed to update transfer.');
			$this->sentryHub->captureException($exception);
		}
	}

	/**
	 * @param Trophy $trophyModel
	 * @param Message $message
	 */
	private function persistTrophy(Trophy $trophyModel, Message $message)
	{
		try {
			$this->trophyRepository->persist($trophyModel);
		} catch (DynamoDBRepositoryException $exception) {
			Event::failed($message, $this->eventName, 'Failed to update trophy.');
			$this->sentryHub->captureException($exception);
		}
	}
}