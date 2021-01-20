<?php


namespace App\Http\Services\TeamsMatch;


use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\Cache\Interfaces\TeamsMatchCacheServiceInterface;


/**
 * Class TeamsMatchService
 * @package App\Http\Services\TeamsMatch
 */
class TeamsMatchService
{
	private TeamsMatchRepository $teamsMatchRepository;
	private TeamsMatchCacheServiceInterface $teamsMatchCacheService;

	/**
	 * TeamsMatchService constructor.
	 * @param TeamsMatchRepository $teamsMatchRepository
	 * @param TeamsMatchCacheServiceInterface $teamsMatchCacheService
	 */
	public function __construct(
		TeamsMatchRepository $teamsMatchRepository,
		TeamsMatchCacheServiceInterface $teamsMatchCacheService
	) {
		$this->teamsMatchRepository = $teamsMatchRepository;
		$this->teamsMatchCacheService = $teamsMatchCacheService;
	}

	/**
	 * @param string $team
	 * @return array
	 */
	public function findTeamsMatchByTeamId(string $team): array
	{
		return $this->teamsMatchCacheService->rememberForeverTeamsMatchOverviewByTeam($team, function () use ($team) {
			$upcoming = $this->teamsMatchRepository->findTeamsMatchByTeamId(
				$team,
				TeamsMatch::STATUS_UPCOMING,
				config('common.limit.team_overview_upcoming')
			);
			$finished = $this->teamsMatchRepository->findTeamsMatchByTeamId(
				$team,
				TeamsMatch::STATUS_FINISHED,
				config('common.limit.team_overview_finished')
			);
			return ['upcoming' => $upcoming, 'finished' => $finished];
		});
	}
}