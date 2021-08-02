<?php


namespace App\Projections\Projector;


use App\Events\Projection\MatchWasCreatedProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Team\Traits\TeamTraits;
use App\Http\Services\TeamsMatch\TeamsMatchService;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\Message;
use App\ValueObjects\ReadModel\TeamName;
use App\Models\ReadModels\Team;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\Services\Cache\Interfaces\TeamsMatchCacheServiceInterface;
use DateTime;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class MatchProjector
 * @package App\Projections\Projector
 */
class MatchProjector
{
	use TeamTraits;

	const SCORES_TYPE_TOTAL = 'total';
	const SCORES_TYPE_PENALTY = 'penalty';
	const MATCH_STATUS_GAME_ENDED = 'gameEnded';

	private TeamsMatchRepository $teamsMatchRepository;
	private TeamCacheServiceInterface $teamCacheService;
	private TeamRepository $teamRepository;
	private TeamsMatchCacheServiceInterface $teamsMatchCacheService;
	private TeamsMatchService $teamsMatchService;
	private SerializerInterface $serializer;
	private string $eventName;

	/**
	 * MatchProjector constructor.
	 * @param TeamsMatchRepository $teamsMatchRepository
	 * @param TeamCacheServiceInterface $teamCacheService
	 * @param TeamRepository $teamRepository
	 * @param TeamsMatchCacheServiceInterface $teamsMatchCacheService
	 * @param TeamsMatchService $teamsMatchService
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TeamsMatchRepository $teamsMatchRepository,
		TeamCacheServiceInterface $teamCacheService,
		TeamRepository $teamRepository,
		TeamsMatchCacheServiceInterface $teamsMatchCacheService,
		TeamsMatchService $teamsMatchService,
		SerializerInterface $serializer
	) {
		$this->teamsMatchRepository = $teamsMatchRepository;
		$this->teamCacheService = $teamCacheService;
		$this->teamRepository = $teamRepository;
		$this->teamsMatchCacheService = $teamsMatchCacheService;
		$this->teamsMatchService = $teamsMatchService;
		$this->serializer = $serializer;
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	public function applyMatchWasCreated(Message $message): void
	{
		$this->eventName = config('mediator-event.events.match_was_created');

		Event::processing($message, $this->eventName);

		$body = $message->getBody();
		$identifier = $body->getIdentifiers();

		$this->checkIdentifiersValidation($message);

		$this->checkMetadataValidation($message);

		$homeTeamsMatchModel = $this->createTeamsMatchModel($message);
		$awayTeamsMatchModel = $this->createTeamsMatchModel($message, false);

		$this->persistTeamsMatch($homeTeamsMatchModel, $message);
		$this->persistTeamsMatch($awayTeamsMatchModel, $message);

		event(new MatchWasCreatedProjectorEvent($message));

		$this->teamsMatchCacheService->forget('teams_match*');

		$this->createTeamsMatchCache([$identifier['home'], $identifier['away']]);

		Event::succeeded($message, $this->eventName);
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	public function applyMatchFinished(Message $message): void
	{
		$this->eventName = config('mediator-event.events.match_finished');

		Event::processing($message, $this->eventName);

		$body = $message->getBody();
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();

		if (empty($identifier['match'])){
			$validationMessage = 'Match field is empty.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}

		if (empty($metadata['scores'])){
			$validationMessage = 'Scores field is empty.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}

		$teamsMatchItems = $this->checkItemExist($identifier['match'], $message);

		$score = $this->excludeScoreTypes($metadata['scores']);

		$this->teamsMatchCacheService->forget('teams_match*');

		if ($identifier['winner'] == "") {
			foreach ($teamsMatchItems as $teamsMatch) {
				/** @var TeamsMatch $teamsMatch */
				$this->updateTeamsMatchByMatchFinishedEvent($teamsMatch, $score, TeamsMatch::EVALUATION_DRAW, $message);
				$this->createTeamsMatchCache([$teamsMatch->getTeamId()]);
			}
			goto successfullyLog;
		}

		foreach ($teamsMatchItems as $teamsMatch) {
			/** @var TeamsMatch $teamsMatch */
			$this->updateTeamsMatchByMatchFinishedEvent(
				$teamsMatch,
				$score,
				($identifier['winner'] == $teamsMatch->getTeamId()) ? TeamsMatch::EVALUATION_WIN : TeamsMatch::EVALUATION_LOSS,
				$message
			);
			$this->createTeamsMatchCache([$teamsMatch->getTeamId()]);
		}

		successfullyLog:
		Event::succeeded($message, $this->eventName);
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	public function applyMatchStatusChanged(Message $message): void
	{
		$this->eventName = config('mediator-event.events.match_status_changed');

		Event::processing($message, $this->eventName);

		$body = $message->getBody();
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();

		if (empty($identifier['match'])){
			$validationMessage = 'Match field is empty.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}

		if (empty($metadata['status'])){
			$validationMessage = 'Status field is empty.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}

		$teamsMatchItems = $this->checkItemExist($identifier['match'], $message);

		$status = ($metadata['status'] == self::MATCH_STATUS_GAME_ENDED) ? TeamsMatch::STATUS_FINISHED : TeamsMatch::STATUS_UNKNOWN;

		$this->teamsMatchCacheService->forget('teams_match*');

		foreach ($teamsMatchItems as $teamsMatch) {
			/** @var TeamsMatch $teamsMatch */
			$this->updateTeamsMatchByMatchStatusChanged($teamsMatch, $status, $message);
			$this->createTeamsMatchCache([$teamsMatch->getTeamId()]);
		}

		Event::succeeded($message, $this->eventName);
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function checkIdentifiersValidation(Message $message): void
	{
		$requiredFields = ['match', 'home', 'away', 'competition'];
		foreach ($requiredFields as $fieldName) {
			if (empty($message->getBody()->getIdentifiers()[$fieldName])) {
				$validationMessage = sprintf("%s field is empty.", ucfirst($fieldName));
				Event::failed($message, $this->eventName, $validationMessage);
				throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
			}
		}
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function checkMetadataValidation(Message $message): void
	{
		$requiredFields = ['date'];
		foreach ($requiredFields as $fieldName) {
			if (empty($message->getBody()->getMetadata()[$fieldName])) {
				$validationMessage = sprintf("%s field is empty.", ucfirst($fieldName));
				Event::failed($message, $this->eventName, $validationMessage);
				throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
			}
		}
	}

	/**
	 * @param Message $message
	 * @param bool $home
	 * @return TeamsMatch
	 * @throws ProjectionException
	 */
	private function createTeamsMatchModel(Message $message, bool $home = true): TeamsMatch
	{
		$body = $message->getBody();
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();

		/** @var Team $homeTeamItem */
		$homeTeamItem = $this->findTeam($identifier['home']);
		if (!$homeTeamItem) {
			$validationMessage = sprintf('Team Item not found by following id: %s', $identifier['home']);
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage);
		}
		$homeTeamName = (new TeamName())
			->setOfficial($homeTeamItem->getName()->getOfficial())
			->setOriginal($homeTeamItem->getName()->getOriginal())
			->setShort($homeTeamItem->getName()->getShort());

		/** @var Team $awayTeamItem */
		$awayTeamItem = $this->findTeam($identifier['away']);
		if (!$awayTeamItem) {
			$validationMessage = sprintf('Team Item not found by following id: %s', $identifier['away']);
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage);
		}
		$awayTeamName = (new TeamName())
			->setOfficial($awayTeamItem->getName()->getOfficial())
			->setOriginal($awayTeamItem->getName()->getOriginal())
			->setShort($awayTeamItem->getName()->getShort());

		return (new TeamsMatch())
			->setCompetitionId($identifier['competition'])
			->setMatchId($identifier['match'])
			->setTeamId($home ? $identifier['home'] : $identifier['away'])
			->setOpponentId($home ? $identifier['away'] : $identifier['home'])
			->setTeamName($home ? $homeTeamName : $awayTeamName)
			->setOpponentName($home ? $awayTeamName : $homeTeamName)
			->setIsHome(($home) ? true : false)
			->setStatus(TeamsMatch::STATUS_UPCOMING)
			->setCoverage(($metadata['coverage']) ? $metadata['coverage'] : TeamsMatch::COVERAGE_LOW)
			->setSortKey(TeamsMatch::generateSortKey($this->generateDateTime($metadata['date'], $metadata['time']), TeamsMatch::STATUS_UPCOMING))
			->setCreatedAt(new DateTime());
	}

	/**
	 * @param string $date
	 * @param string|null $time
	 * @return DateTime
	 * @throws Exception
	 */
	private function generateDateTime(string $date, ?string $time): DateTime
	{
		$dateTime = (new DateTime($date))->setTime(0, 0, 0);
		if ($time) {
			$dateTime = new DateTime($date . $time);
		}
		return $dateTime;
	}

	/**
	 * @param string $match
	 * @param Message $message
	 * @return array
	 * @throws ProjectionException
	 */
	private function checkItemExist(string $match, Message $message): array {
		$teamsMatchItems = $this->teamsMatchRepository->findTeamsMatchByMatchId($match);
		if (!$teamsMatchItems) {
			$validationMessage = 'TeamsMatch items not found.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage);
		}
		return $teamsMatchItems;
	}

	/**
	 * @param $scores
	 * @return array|mixed
	 */
	private function excludeScoreTypes($scores): array
	{
		$score = [];
		foreach ($scores as $scoreItem) {
			if (in_array($scoreItem['type'], [self::SCORES_TYPE_TOTAL, self::SCORES_TYPE_PENALTY]) ) {
				$score[] = $scoreItem;
			}
		}
		return $score;
	}

	/**
	 * @param TeamsMatch $teamsMatch
	 * @param array $score
	 * @param string $evaluation
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function updateTeamsMatchByMatchFinishedEvent(TeamsMatch $teamsMatch, array $score, string $evaluation, Message $message): void
	{
		$formattedScore = [];
		foreach ($score as $item) {
			$formattedScore[$item['type']] =  [
				'home' => $item['home'],
				'away' => $item['away']
			];
		}
		$teamsMatch
			->setEvaluation($evaluation)
			->setStatus(TeamsMatch::STATUS_FINISHED)
			->setSortKey(TeamsMatch::generateSortKey(TeamsMatch::getMatchDate($teamsMatch->getSortKey()), TeamsMatch::STATUS_FINISHED))
			->setResult($formattedScore);
		$this->persistTeamsMatch($teamsMatch, $message);
	}

	/**
	 * @param TeamsMatch $teamsMatch
	 * @param string $status
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function updateTeamsMatchByMatchStatusChanged(TeamsMatch $teamsMatch, string $status, Message $message): void
	{
		$teamsMatch
			->setStatus($status)
			->setSortKey(TeamsMatch::generateSortKey(TeamsMatch::getMatchDate($teamsMatch->getSortKey()), $status));
		$this->persistTeamsMatch($teamsMatch, $message);
	}

	/**
	 * @param TeamsMatch $teamsMatch
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function persistTeamsMatch(TeamsMatch $teamsMatch, Message $message): void
	{
		try {
			$this->teamsMatchRepository->persist($teamsMatch);
		} catch (DynamoDBRepositoryException $exception) {
			$validationMessage = 'Failed to persist teamsMatch.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, $exception->getCode(), $exception);
		}
	}

	/**
	 * @param array $teams
	 */
	private function createTeamsMatchCache(array $teams): void
	{
		try {
			foreach ($teams as $teamId) {
				$this->teamsMatchService->getTeamsMatchInfo($teamId);
			}
		} catch (Exception $e) {
		}
	}
}