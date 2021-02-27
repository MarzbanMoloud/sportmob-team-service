<?php


namespace App\Projections\Projector;


use App\Events\Projection\TrophyProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Trophy\TrophyService;
use App\Models\ReadModels\Team;
use App\Models\ReadModels\Trophy;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TrophyRepository;
use App\Services\Cache\Interfaces\TrophyCacheServiceInterface;
use App\Services\Cache\TrophyCacheService;
use App\ValueObjects\Broker\Mediator\MessageBody;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TrophyProjector
 * @package App\Projections\Projector
 */
class TrophyProjector
{
	private TrophyRepository $trophyRepository;
	private TeamRepository $teamRepository;
	private TrophyService $trophyService;
	private LoggerInterface $logger;
	private SerializerInterface $serializer;
	private TrophyCacheServiceInterface $trophyCacheService;
	private string $eventName;

	/**
	 * TrophyProjector constructor.
	 * @param TrophyRepository $trophyRepository
	 * @param TeamRepository $teamRepository
	 * @param TrophyService $trophyService
	 * @param TrophyCacheServiceInterface $trophyCacheService
	 * @param LoggerInterface $logger
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TrophyRepository $trophyRepository,
		TeamRepository $teamRepository,
		TrophyService $trophyService,
		TrophyCacheServiceInterface $trophyCacheService,
		LoggerInterface $logger,
		SerializerInterface $serializer
	) {
		$this->trophyRepository = $trophyRepository;
		$this->teamRepository = $teamRepository;
		$this->trophyService = $trophyService;
		$this->logger = $logger;
		$this->serializer = $serializer;
		$this->trophyCacheService = $trophyCacheService;
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyTeamBecameWinner(MessageBody $body): void
	{
		$this->eventName = config('mediator-event.events.team_became_winner');
		$this->logger->alert(
			sprintf("%s handler in progress.", $this->eventName),
			$this->serializer->normalize($body, 'array')
		);
		$this->applyEventByPosition($body, Trophy::POSITION_WINNER);
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyTeamBecameRunnerUp(MessageBody $body): void
	{
		$this->eventName = config('mediator-event.events.team_became_runner_up');
		$this->logger->alert(
			sprintf("%s handler in progress.", $this->eventName),
			$this->serializer->normalize($body, 'array')
		);
		$this->applyEventByPosition($body, Trophy::POSITION_RUNNER_UP);
	}

	/**
	 * @param MessageBody $body
	 * @param string $position
	 * @throws ProjectionException
	 */
	private function applyEventByPosition(MessageBody $body, string $position)
	{
		$identifiers = $body->getIdentifiers();
		$this->checkIdentifiersValidation($identifiers);
		/** @var Team $team */
		if (!$team = $this->teamRepository->find(['id' => $identifiers['team']])) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					sprintf('Could not find team by given Id : %s', $identifiers['team'])
				), $identifiers
			);
			throw new ProjectionException(sprintf('Could not find team by given Id : %s', $identifiers['team']));
		}
		$trophyModel = $this->createTrophyModel($identifiers, $team, $position);
		$trophyModel->prePersist();
		$this->persistTrophy($trophyModel);
		event(new TrophyProjectorEvent($trophyModel, $this->eventName));
		/** Create cache by call service */
		try {
			$this->trophyCacheService->forget(TrophyCacheService::getTrophyByCompetitionKey($identifiers['competition']));
			$this->trophyCacheService->forget(TrophyCacheService::getTrophyByTeamKey($identifiers['team']));
			$this->trophyService->getTrophiesByTeam($identifiers['team']);
			$this->trophyService->getTrophiesByCompetition($identifiers['competition']);
		} catch (\Exception $e) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Failed create cache for trophy.'
				), $this->serializer->normalize($trophyModel, 'array')
			);
		}
		$this->logger->alert(
			sprintf("%s handler completed successfully.", $this->eventName),
			$this->makeContextForLog($trophyModel)
		);
	}

	/**
	 * @param array $identifiers
	 * @throws ProjectionException
	 */
	private function checkIdentifiersValidation(array $identifiers): void
	{
		$requiredFields = [
			'competition' => 'Competition',
			'tournament' => 'Tournament',
			'team' => 'Team'
		];
		foreach ($requiredFields as $fieldName => $prettyFieldName) {
			if (empty($identifiers[$fieldName])) {
				$this->logger->alert(
					sprintf(
						"%s handler failed because of %s",
						$this->eventName,
						sprintf("%s field is empty.", $prettyFieldName)
					), $identifiers
				);
				throw new ProjectionException(
					sprintf("%s field is empty.", $prettyFieldName),
					ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR
				);
			}
		}
	}

	/**
	 * @param array $identifiers
	 * @param Team $team
	 * @param $position
	 * @return Trophy
	 */
	private function createTrophyModel(array $identifiers, Team $team, string $position): Trophy
	{
		return (new Trophy())
			->setCompetitionId($identifiers['competition'])
			->setTournamentId($identifiers['tournament'])
			->setTeamId($identifiers['team'])
			->setTeamName($team->getName()->getOfficial())
			->setPosition($position);
	}

	/**
	 * @param Trophy $trophyModel
	 * @throws ProjectionException
	 */
	private function persistTrophy(Trophy $trophyModel): void
	{
		try {
			$this->trophyRepository->persist($trophyModel);
		} catch (DynamoDBRepositoryException $exception) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Failed to persist trophy.'
				), $this->makeContextForLog($trophyModel)
			);
			throw new ProjectionException('Failed to persist trophy.', $exception->getCode(), $exception);
		}
	}

	/**
	 * @param Trophy $trophy
	 * @return array
	 */
	private function makeContextForLog(Trophy $trophy): array
	{
		$trophyArray = $this->serializer->normalize($trophy, 'array');
		$temp['_teamName'] = $trophyArray['teamName'];
		unset($trophyArray['teamName']);
		return $trophyArray;
	}
}