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
use App\ValueObjects\Broker\Mediator\MessageBody;


/**
 * Class TrophyProjector
 * @package App\Projections\Projector
 */
class TrophyProjector
{
	private TrophyRepository $trophyRepository;
	private TeamRepository $teamRepository;
	private TrophyService $trophyService;

	/**
	 * TrophyProjector constructor.
	 * @param TrophyRepository $trophyRepository
	 * @param TeamRepository $teamRepository
	 * @param TrophyService $trophyService
	 */
	public function __construct(
		TrophyRepository $trophyRepository,
		TeamRepository $teamRepository,
		TrophyService $trophyService
	) {
		$this->trophyRepository = $trophyRepository;
		$this->teamRepository = $teamRepository;
		$this->trophyService = $trophyService;
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyTeamBecameWinner(MessageBody $body): void
	{
		$this->applyEventByPosition($body, Trophy::POSITION_WINNER);
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyTeamBecameRunnerUp(MessageBody $body): void
	{
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
		if (!$team = $this->teamRepository->find(['id' => $identifiers['team']])) {
			throw new ProjectionException(sprintf('Could not find team by given Id : %s', $identifiers['team']));
		}
		$trophyModel = $this->createTrophyModel($identifiers, $team, $position);
		$trophyModel->prePersist();
		$this->persistTrophy($trophyModel);
		event(new TrophyProjectorEvent($trophyModel));
		/** Create cache by call service */
		$this->trophyService->getTrophiesByTeam($identifiers['team']);
		$this->trophyService->getTrophiesByCompetition($identifiers['competition']);
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
			throw new ProjectionException('Failed to persist trophy.', $exception->getCode(), $exception);
		}
	}
}