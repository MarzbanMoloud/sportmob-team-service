<?php


namespace App\Http\Services\TeamsMatch;


use App\Http\Services\Team\Traits\TeamTraits;
use App\Models\ReadModels\Team;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\Services\Cache\Interfaces\TeamsMatchCacheServiceInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Class TeamsMatchService
 * @package App\Http\Services\TeamsMatch
 */
class TeamsMatchService
{
	use TeamTraits;

	private TeamsMatchRepository $teamsMatchRepository;
	private TeamsMatchCacheServiceInterface $teamsMatchCacheService;
	private TeamCacheServiceInterface $teamCacheService;
	private TeamRepository $teamRepository;

	/**
	 * TeamsMatchService constructor.
	 * @param TeamsMatchRepository $teamsMatchRepository
	 * @param TeamsMatchCacheServiceInterface $teamsMatchCacheService
	 * @param TeamCacheServiceInterface $teamCacheService
	 * @param TeamRepository $teamRepository
	 */
	public function __construct(
		TeamsMatchRepository $teamsMatchRepository,
		TeamsMatchCacheServiceInterface $teamsMatchCacheService,
		TeamCacheServiceInterface $teamCacheService,
		TeamRepository $teamRepository
	) {
		$this->teamsMatchRepository = $teamsMatchRepository;
		$this->teamsMatchCacheService = $teamsMatchCacheService;
		$this->teamCacheService = $teamCacheService;
		$this->teamRepository = $teamRepository;
	}

	/**
	 * @param string $team
	 * @return array
	 */
	public function getTeamsMatchInfo(string $team): array
	{
		/** @var Team $teamItem */
		$teamItem = $this->findTeam($team);
		if (!$teamItem) {
			throw new NotFoundHttpException();
		}

		return [
			'team' => [
				'id' => $teamItem->getId(),
				'name' => [
					'original' => $teamItem->getName()->getOriginal(),
					'short' => $teamItem->getName()->getShort(),
					'official' => $teamItem->getName()->getOfficial(),
				]
			],
			TeamsMatch::STATUS_UPCOMING =>
				$this->findTeamsMatchByTeamId($team, TeamsMatch::STATUS_UPCOMING, config('common.limit.team_overview_upcoming')),
			TeamsMatch::STATUS_FINISHED =>
				$this->findTeamsMatchByTeamId($team, TeamsMatch::STATUS_FINISHED, config('common.limit.team_overview_finished'))
		];
	}

	/**
	 * @param string $team
	 * @param string $status
	 * @param int $limit
	 * @return mixed
	 */
	private function findTeamsMatchByTeamId(string $team, string $status, int $limit)
	{
		return $this->teamsMatchCacheService->rememberForeverTeamsMatchByTeamId($team, $status, function () use ($team, $status, $limit) {
			return $this->teamsMatchRepository->findTeamsMatchByTeamId($team, $status, $limit);
		});
	}
}