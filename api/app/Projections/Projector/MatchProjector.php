<?php


namespace App\Projections\Projector;


use App\Events\Projection\MatchWasCreatedProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Team\Traits\TeamTraits;
use App\Http\Services\TeamsMatch\TeamsMatchService;
use App\ValueObjects\ReadModel\TeamName;
use App\Models\ReadModels\Team;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\Services\Cache\Interfaces\TeamsMatchCacheServiceInterface;
use App\ValueObjects\Broker\Mediator\MessageBody;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
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
	private LoggerInterface $logger;
	private SerializerInterface $serializer;
	private string $eventName;

	/**
	 * MatchProjector constructor.
	 * @param TeamsMatchRepository $teamsMatchRepository
	 * @param TeamCacheServiceInterface $teamCacheService
	 * @param TeamRepository $teamRepository
	 * @param TeamsMatchCacheServiceInterface $teamsMatchCacheService
	 * @param TeamsMatchService $teamsMatchService
	 * @param LoggerInterface $logger
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TeamsMatchRepository $teamsMatchRepository,
		TeamCacheServiceInterface $teamCacheService,
		TeamRepository $teamRepository,
		TeamsMatchCacheServiceInterface $teamsMatchCacheService,
		TeamsMatchService $teamsMatchService,
		LoggerInterface $logger,
		SerializerInterface $serializer
	) {
		$this->teamsMatchRepository = $teamsMatchRepository;
		$this->teamCacheService = $teamCacheService;
		$this->teamRepository = $teamRepository;
		$this->teamsMatchCacheService = $teamsMatchCacheService;
		$this->teamsMatchService = $teamsMatchService;
		$this->logger = $logger;
		$this->serializer = $serializer;
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyMatchWasCreated(MessageBody $body): void
	{
		$this->eventName = config('mediator-event.events.match_was_created');
		$this->logger->alert(
			sprintf("%s handler in progress.", $this->eventName),
			$this->serializer->normalize($body, 'array')
		);
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		$this->checkIdentifiersValidation($body);
		$this->checkMetadataValidation($body);
		$homeTeamsMatchModel = $this->createTeamsMatchModel($identifier, $metadata);
		$awayTeamsMatchModel = $this->createTeamsMatchModel($identifier, $metadata, false);
		$this->persistTeamsMatch($homeTeamsMatchModel);
		$this->persistTeamsMatch($awayTeamsMatchModel);
		event(new MatchWasCreatedProjectorEvent($identifier));
		/** Create cache by call service */
		$this->teamsMatchCacheService->forget('teams_match*');
		$this->createTeamsMatchCache($identifier['home'], $this->serializer->normalize($body, 'array'));
		$this->createTeamsMatchCache($identifier['away'], $this->serializer->normalize($body, 'array'));
		$this->logger->alert(
			sprintf("%s handler completed successfully.", $this->eventName),
			[
				'home' => $this->serializer->normalize($homeTeamsMatchModel, 'array'),
				'away' => $this->serializer->normalize($awayTeamsMatchModel, 'array'),
			]
		);
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyMatchFinished(MessageBody $body): void
	{
		$this->eventName = config('mediator-event.events.match_finished');
		$this->logger->alert(
			sprintf("%s handler in progress.", $this->eventName),
			$this->serializer->normalize($body, 'array')
		);
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		if (empty($identifier['match'])){
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Match field is empty.'
				), $this->serializer->normalize($body, 'array')
			);
			throw new ProjectionException('Match field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		if (empty($metadata['scores'])){
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Scores field is empty.'
				), $this->serializer->normalize($body, 'array')
			);
			throw new ProjectionException('Scores field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		$teamsMatchItems = $this->checkItemExist($identifier['match'], $this->serializer->normalize($body, 'array'));
		$score = $this->excludeScoreTypes($metadata['scores']);
		if ($identifier['winner'] == "") {
			foreach ($teamsMatchItems as $teamsMatch) {
				/** @var TeamsMatch $teamsMatch */
				$this->updateTeamsMatchByMatchFinishedEvent($teamsMatch, $score, TeamsMatch::EVALUATION_DRAW);
				$this->teamsMatchCacheService->forget('teams_match*');
				$this->createTeamsMatchCache($teamsMatch->getTeamId(), $this->serializer->normalize($body, 'array'));
			}
			goto successfullyLog;
		}
		foreach ($teamsMatchItems as $teamsMatch) {
			/** @var TeamsMatch $teamsMatch */
			$this->updateTeamsMatchByMatchFinishedEvent(
				$teamsMatch,
				$score,
				($identifier['winner'] == $teamsMatch->getTeamId()) ? TeamsMatch::EVALUATION_WIN : TeamsMatch::EVALUATION_LOSS
			);
			$this->teamsMatchCacheService->forget('teams_match*');
			$this->createTeamsMatchCache($teamsMatch->getTeamId(), $this->serializer->normalize($body, 'array'));
		}
		successfullyLog:
		$this->logger->alert(
			sprintf("%s handler completed successfully.", $this->eventName),
			$this->serializer->normalize($teamsMatchItems, 'array')
		);
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyMatchStatusChanged(MessageBody $body): void
	{
		$this->eventName = config('mediator-event.events.match_status_changed');
		$this->logger->alert(
			sprintf("%s handler in progress.", $this->eventName),
			$this->serializer->normalize($body, 'array')
		);
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		if (empty($identifier['match'])){
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Match field is empty.'
				), $this->serializer->normalize($body, 'array')
			);
			throw new ProjectionException('Match field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		if (empty($metadata['status'])){
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Status field is empty.'
				), $this->serializer->normalize($body, 'array')
			);
			throw new ProjectionException('Status field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		$teamsMatchItems = $this->checkItemExist($identifier['match'], $this->serializer->normalize($body, 'array'));
		$status = ($metadata['status'] == self::MATCH_STATUS_GAME_ENDED) ? TeamsMatch::STATUS_FINISHED : TeamsMatch::STATUS_UNKNOWN;
		foreach ($teamsMatchItems as $teamsMatch) {
			/** @var TeamsMatch $teamsMatch */
			$this->updateTeamsMatchByMatchStatusChanged($teamsMatch, $status);
			$this->teamsMatchCacheService->forget('teams_match*');
			$this->createTeamsMatchCache($teamsMatch->getTeamId(), $this->serializer->normalize($body, 'array'));
		}
		$this->logger->alert(
			sprintf("%s handler completed successfully.", $this->eventName),
			$this->serializer->normalize($teamsMatchItems, 'array')
		);
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	private function checkIdentifiersValidation(MessageBody $body): void
	{
		$requiredFields = ['match', 'home', 'away', 'competition'];
		foreach ($requiredFields as $fieldName) {
			if (empty($body->getIdentifiers()[$fieldName])) {
				$this->logger->alert(
					sprintf(
						"%s handler failed because of %s",
						$this->eventName,
						sprintf("%s field is empty.", $fieldName)
					), $this->serializer->normalize($body, 'array')
				);
				throw new ProjectionException(
					sprintf("%s field is empty.", ucfirst($fieldName)),
					ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR
				);
			}
		}
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	private function checkMetadataValidation(MessageBody $body): void
	{
		$requiredFields = ['date'];
		foreach ($requiredFields as $fieldName) {
			if (empty($body->getMetadata()[$fieldName])) {
				$this->logger->alert(
					sprintf(
						"%s handler failed because of %s",
						$this->eventName,
						sprintf("%s field is empty.", $fieldName)
					), $this->serializer->normalize($body, 'array')
				);
				throw new ProjectionException(
					sprintf("%s field is empty.", ucfirst($fieldName)),
					ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR
				);
			}
		}
	}

	/**
	 * @param array $identifier
	 * @param array $metadata
	 * @param bool $home
	 * @return TeamsMatch
	 * @throws Exception
	 */
	private function createTeamsMatchModel(array $identifier, array $metadata, bool $home = true): TeamsMatch
	{
		/** @var Team $homeTeamItem */
		$homeTeamItem = $this->findTeam($identifier['home']);
		if (!$homeTeamItem) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					sprintf('Team Item not found by following id: %s', $identifier['home'])
				), ['identifier' => $identifier, 'metadata' => $metadata]
			);
			throw new ProjectionException();
		}
		$homeTeamName = (new TeamName())
			->setOfficial($homeTeamItem->getName()->getOfficial())
			->setOriginal($homeTeamItem->getName()->getOriginal())
			->setShort($homeTeamItem->getName()->getShort());

		/** @var Team $awayTeamItem */
		$awayTeamItem = $this->findTeam($identifier['away']);
		if (!$awayTeamItem) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					sprintf('Team Item not found by following id: %s', $identifier['away'])
				), ['identifier' => $identifier, 'metadata' => $metadata]
			);
			throw new ProjectionException();
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
			->setSortKey(TeamsMatch::generateSortKey($this->generateDateTime($metadata['date'], $metadata['time']), TeamsMatch::STATUS_UPCOMING));
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
	 * @param array $body
	 * @return array
	 * @throws ProjectionException
	 */
	private function checkItemExist(string $match, array $body): array {
		$teamsMatchItems = $this->teamsMatchRepository->findTeamsMatchByMatchId($match);
		if (!$teamsMatchItems) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'TeamsMatch items not found.'
				), $body
			);
			throw new ProjectionException('TeamsMatch items not found.');
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
	 * @throws ProjectionException
	 */
	private function updateTeamsMatchByMatchFinishedEvent(TeamsMatch $teamsMatch, array $score, string $evaluation): void
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
		$this->persistTeamsMatch($teamsMatch);
	}

	/**
	 * @param TeamsMatch $teamsMatch
	 * @param string $status
	 * @throws ProjectionException
	 */
	private function updateTeamsMatchByMatchStatusChanged(TeamsMatch $teamsMatch, string $status): void
	{
		$teamsMatch
			->setStatus($status)
			->setSortKey(TeamsMatch::generateSortKey(TeamsMatch::getMatchDate($teamsMatch->getSortKey()), $status));
		$this->persistTeamsMatch($teamsMatch);
	}

	/**
	 * @param TeamsMatch $teamsMatch
	 * @throws ProjectionException
	 */
	private function persistTeamsMatch(TeamsMatch $teamsMatch): void
	{
		try {
			$this->teamsMatchRepository->persist($teamsMatch);
		} catch (DynamoDBRepositoryException $exception) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Failed to persist teamsMatch.'
				), $this->serializer->normalize($teamsMatch, 'array')
			);
			throw new ProjectionException('Failed to persist teamsMatch.', $exception->getCode(), $exception);
		}
	}

	/**
	 * @param string $teamId
	 * @param array $body
	 */
	private function createTeamsMatchCache(string $teamId, array $body): void
	{
		try {
			$this->teamsMatchService->getTeamsMatchInfo($teamId);
		} catch (Exception $e) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Failed create cache for match.'
				),
				$body
			);
		}
	}
}