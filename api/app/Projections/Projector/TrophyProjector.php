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
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\Message;
use DateTime;
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
	private SerializerInterface $serializer;
	private TrophyCacheServiceInterface $trophyCacheService;
	private string $eventName;

	/**
	 * TrophyProjector constructor.
	 * @param TrophyRepository $trophyRepository
	 * @param TeamRepository $teamRepository
	 * @param TrophyService $trophyService
	 * @param TrophyCacheServiceInterface $trophyCacheService
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TrophyRepository $trophyRepository,
		TeamRepository $teamRepository,
		TrophyService $trophyService,
		TrophyCacheServiceInterface $trophyCacheService,
		SerializerInterface $serializer
	) {
		$this->trophyRepository = $trophyRepository;
		$this->teamRepository = $teamRepository;
		$this->trophyService = $trophyService;
		$this->serializer = $serializer;
		$this->trophyCacheService = $trophyCacheService;
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	public function applyTeamBecameWinner(Message $message): void
	{
		$this->eventName = config('mediator-event.events.team_became_winner');
		Event::processing($message, $this->eventName);
		$this->applyEventByPosition($message, Trophy::POSITION_WINNER);
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	public function applyTeamBecameRunnerUp(Message $message): void
	{
		$this->eventName = config('mediator-event.events.team_became_runner_up');
		Event::processing($message, $this->eventName);
		$this->applyEventByPosition($message, Trophy::POSITION_RUNNER_UP);
	}

	/**
	 * @param Message $message
	 * @param string $position
	 * @throws ProjectionException
	 */
	private function applyEventByPosition(Message $message, string $position)
	{
		$body = $message->getBody();
		$identifiers = $body->getIdentifiers();
		$this->checkIdentifiersValidation($message);
		/** @var Team $team */
		if (!$team = $this->teamRepository->find(['id' => $identifiers['team']])) {
			$validationMessage = sprintf('Could not find team by given Id : %s', $identifiers['team']);
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage);
		}
		$trophyModel = $this->createTrophyModel($identifiers, $team, $position);
		$trophyModel->prePersist();
		$this->persistTrophy($trophyModel, $message);
		event(new TrophyProjectorEvent($trophyModel, $this->eventName, $message));
		/** Create cache by call service */
		try {
			$this->trophyCacheService->forget(TrophyCacheService::getTrophyByCompetitionKey($identifiers['competition']));
			$this->trophyCacheService->forget(TrophyCacheService::getTrophyByTeamKey($identifiers['team']));
			$this->trophyService->getTrophiesByTeam($identifiers['team']);
			$this->trophyService->getTrophiesByCompetition($identifiers['competition']);
		} catch (\Exception $e) {
		}
		Event::succeeded($message, $this->eventName);
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function checkIdentifiersValidation(Message $message): void
	{
		$requiredFields = [
			'competition' => 'Competition',
			'tournament' => 'Tournament',
			'team' => 'Team'
		];
		foreach ($requiredFields as $fieldName => $prettyFieldName) {
			if (empty($message->getBody()->getIdentifiers()[$fieldName])) {
				$validationMessage = sprintf("%s field is empty.", $prettyFieldName);
				Event::failed($message, $this->eventName, $validationMessage);
				throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
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
			->setTeamName($team->getName()->getOriginal())
			->setPosition($position)
			->setCreatedAt(new DateTime());
	}

	/**
	 * @param Trophy $trophyModel
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function persistTrophy(Trophy $trophyModel, Message $message): void
	{
		try {
			$this->trophyRepository->persist($trophyModel);
		} catch (DynamoDBRepositoryException $exception) {
			$validationMessage = 'Failed to persist trophy.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, $exception->getCode(), $exception);
		}
	}
}