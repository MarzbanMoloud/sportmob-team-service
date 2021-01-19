<?php


namespace App\Projections\Projector;


use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Models\ReadModels\Embedded\TeamName;
use App\Models\ReadModels\Team;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\ValueObjects\Broker\Mediator\MessageBody;
use DateTime;
use Exception;


/**
 * Class MatchProjector
 * @package App\Projections\Projector
 */
class MatchProjector
{
	const SCORES_TYPE_TOTAL = 'total';
	const MATCH_STATUS_GAME_ENDED = 'gameEnded';

	private TeamsMatchRepository $teamsMatchRepository;
	private TeamCacheServiceInterface $teamCacheService;
	private TeamRepository $teamRepository;

	/**
	 * MatchProjector constructor.
	 * @param TeamsMatchRepository $teamsMatchRepository
	 * @param TeamCacheServiceInterface $teamCacheService
	 * @param TeamRepository $teamRepository
	 */
	public function __construct(
		TeamsMatchRepository $teamsMatchRepository,
		TeamCacheServiceInterface $teamCacheService,
		TeamRepository $teamRepository
	) {
		$this->teamsMatchRepository = $teamsMatchRepository;
		$this->teamCacheService = $teamCacheService;
		$this->teamRepository = $teamRepository;
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
				$this->updateTeamsMatchByMatchFinishedEvent($teamsMatch, $score, TeamsMatch::EVALUATION_DRAW);
			}
			return;
		}
		foreach ($teamsMatchItems as $teamsMatch) {
			$this->updateTeamsMatchByMatchFinishedEvent(
				$teamsMatch,
				$score,
				($identifier['winner'] == $teamsMatch->getTeamId()) ? TeamsMatch::EVALUATION_WIN : TeamsMatch::EVALUATION_LOSS
			);
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
		$requiredFields = ['match', 'home', 'away'];
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
		/**
		 * @var Team $homeTeamItem
		 */
		if (!$homeTeamItem = $this->teamCacheService->getTeam($identifier['home'])) {
			$homeTeamItem = $this->teamRepository->find(['id' => $identifier['home']]);
		}
		if (!$homeTeamItem) {
			throw new ProjectionException();
		}
		$homeTeamName = (new TeamName())
			->setOfficial($homeTeamItem->getName()->getOfficial())
			->setOriginal($homeTeamItem->getName()->getOriginal())
			->setShort($homeTeamItem->getName()->getShort());
		/**
		 * @var Team $awayTeamItem
		 */
		if (!$awayTeamItem = $this->teamCacheService->getTeam($identifier['away'])) {
			$awayTeamItem = $this->teamRepository->find(['id' => $identifier['away']]);
		}
		if (!$awayTeamItem) {
			throw new ProjectionException();
		}
		$awayTeamName = (new TeamName())
			->setOfficial($awayTeamItem->getName()->getOfficial())
			->setOriginal($awayTeamItem->getName()->getOriginal())
			->setShort($awayTeamItem->getName()->getShort());

		return (new TeamsMatch())
			->setMatchId($identifier['match'])
			->setTeamId($home ? $identifier['home'] : $identifier['away'])
			->setOpponentId($home ? $identifier['away'] : $identifier['home'])
			->setTeamName($home ? $homeTeamName : $awayTeamName)
			->setOpponentName($home ? $awayTeamName : $homeTeamName)
			->setIsHome(($home) ? true : false)
			->setStatus(TeamsMatch::STATUS_UPCOMING)
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
			if ($scoreItem['type'] == self::SCORES_TYPE_TOTAL) {
				$score = $scoreItem;
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
			->setResult(
				($score) ?
					[
						$score['type'] => [
							'home' => $score['home'],
							'away' => $score['away']
						]
					] : []
			);
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
			throw new ProjectionException('Failed to persist teamsMatch.', $exception->getCode());
		}
	}
}