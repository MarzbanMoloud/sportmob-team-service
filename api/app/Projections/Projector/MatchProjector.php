<?php


namespace App\Projections\Projector;


use App\Events\Projection\MatchWasCreatedProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Team\Traits\TeamTraits;
use App\Models\ReadModels\Embedded\TeamName;
use App\Models\ReadModels\Team;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\Services\Cache\Interfaces\TeamsMatchCacheServiceInterface;
use App\Services\Cache\TeamsMatchCacheService;
use App\ValueObjects\Broker\Mediator\MessageBody;
use DateTime;
use Exception;


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

	/**
	 * MatchProjector constructor.
	 * @param TeamsMatchRepository $teamsMatchRepository
	 * @param TeamCacheServiceInterface $teamCacheService
	 * @param TeamRepository $teamRepository
	 * @param TeamsMatchCacheServiceInterface $teamsMatchCacheService
	 */
	public function __construct(
		TeamsMatchRepository $teamsMatchRepository,
		TeamCacheServiceInterface $teamCacheService,
		TeamRepository $teamRepository,
		TeamsMatchCacheServiceInterface $teamsMatchCacheService
	) {
		$this->teamsMatchRepository = $teamsMatchRepository;
		$this->teamCacheService = $teamCacheService;
		$this->teamRepository = $teamRepository;
		$this->teamsMatchCacheService = $teamsMatchCacheService;
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyMatchWasCreated(MessageBody $body): void
	{
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		$this->checkIdentifiersValidation($identifier);
		$this->checkMetadataValidation($metadata);
		$homeTeamsMatchModel = $this->createTeamsMatchModel($identifier, $metadata);
		$awayTeamsMatchModel = $this->createTeamsMatchModel($identifier, $metadata, false);
		$this->persistTeamsMatch($homeTeamsMatchModel);
		$this->persistTeamsMatch($awayTeamsMatchModel);
		$this->removeTeamsMatchCache($identifier['home']);
		event(new MatchWasCreatedProjectorEvent($identifier['competition']));
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyMatchFinished(MessageBody $body): void
	{
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		if (empty($identifier['match'])){
			throw new ProjectionException('Match field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		if (empty($metadata['scores'])){
			throw new ProjectionException('Scores field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		$teamsMatchItems = $this->checkItemExist($identifier['match']);
		$score = $this->excludeScoreTypes($metadata['scores']);
		if ($identifier['winner'] == "") {
			foreach ($teamsMatchItems as $teamsMatch) {
				/** @var TeamsMatch $teamsMatch */
				$this->updateTeamsMatchByMatchFinishedEvent($teamsMatch, $score, TeamsMatch::EVALUATION_DRAW);
				$this->removeTeamsMatchCache($teamsMatch->getTeamId());
			}
			return;
		}
		foreach ($teamsMatchItems as $teamsMatch) {
			/** @var TeamsMatch $teamsMatch */
			$this->updateTeamsMatchByMatchFinishedEvent(
				$teamsMatch,
				$score,
				($identifier['winner'] == $teamsMatch->getTeamId()) ? TeamsMatch::EVALUATION_WIN : TeamsMatch::EVALUATION_LOSS
			);
			$this->removeTeamsMatchCache($teamsMatch->getTeamId());
		}
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyMatchStatusChanged(MessageBody $body): void
	{
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		if (empty($identifier['match'])){
			throw new ProjectionException('Match field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		if (empty($metadata['status'])){
			throw new ProjectionException('Status field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		$teamsMatchItems = $this->checkItemExist($identifier['match']);
		$status = ($metadata['status'] == self::MATCH_STATUS_GAME_ENDED) ? TeamsMatch::STATUS_FINISHED : TeamsMatch::STATUS_UNKNOWN;
		foreach ($teamsMatchItems as $teamsMatch) {
			$this->updateTeamsMatchByMatchStatusChanged($teamsMatch, $status);
		}
	}

	/**
	 * @param array $identifier
	 * @throws ProjectionException
	 */
	private function checkIdentifiersValidation(array $identifier): void
	{
		$requiredFields = ['match', 'home', 'away', 'competition'];
		foreach ($requiredFields as $fieldName) {
			if (empty($identifier[$fieldName])) {
				throw new ProjectionException(
					sprintf("%s field is empty.", ucfirst($fieldName)),
					ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR
				);
			}
		}
	}

	/**
	 * @param array $metadata
	 * @throws ProjectionException
	 */
	private function checkMetadataValidation(array $metadata): void
	{
		$requiredFields = ['date' , 'time'];
		foreach ($requiredFields as $fieldName) {
			if (empty($metadata[$fieldName])) {
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
			throw new ProjectionException();
		}
		$homeTeamName = (new TeamName())
			->setOfficial($homeTeamItem->getName()->getOfficial())
			->setOriginal($homeTeamItem->getName()->getOriginal())
			->setShort($homeTeamItem->getName()->getShort());

		/** @var Team $awayTeamItem */
		$awayTeamItem = $this->findTeam($identifier['away']);
		if (!$awayTeamItem) {
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
			->setSortKey(TeamsMatch::generateSortKey(new DateTime($metadata['date'] . $metadata['time']), TeamsMatch::STATUS_UPCOMING));
	}

	/**
	 * @param $match
	 * @return array
	 * @throws ProjectionException
	 */
	private function checkItemExist($match
	): array {
		$teamsMatchItems = $this->teamsMatchRepository->findTeamsMatchByMatchId($match);
		if (!$teamsMatchItems) {
			throw new ProjectionException('TeamsMatch items not found!');
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
		$teamsMatch
			->setEvaluation($evaluation)
			->setStatus(TeamsMatch::STATUS_FINISHED)
			->setSortKey(TeamsMatch::generateSortKey(TeamsMatch::getMatchDate($teamsMatch->getSortKey()), TeamsMatch::STATUS_FINISHED))
			->setResult(array_map(function ($item){
				return [
					$item['type'] => [
						'home' => $item['home'],
						'away' => $item['away']
					]
				];
			}, $score));
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
	 * @param string $teamId
	 */
	private function removeTeamsMatchCache(string $teamId): void
	{
		foreach ([TeamsMatch::STATUS_UPCOMING, TeamsMatch::STATUS_FINISHED] as $status) {
			if ($this->teamsMatchCacheService->hasTeamsMatchByTeamId($teamId, $status)) {
				$this->teamsMatchCacheService->forget(
					TeamsMatchCacheService::getTeamsMatchByTeamIdKey(
						$teamId,
						$status
					)
				);
			}
		}
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
			throw new ProjectionException('Failed to persist teamsMatch.', $exception->getCode(), $exception);
		}
	}
}