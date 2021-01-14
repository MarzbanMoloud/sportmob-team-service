<?php


namespace App\Http\Services\Trophy;


use App\Models\ReadModels\Trophy;
use App\Models\Repositories\TrophyRepository;
use App\Services\Cache\Interfaces\TrophyCacheServiceInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Class TrophyService
 * @package App\Http\Services\Trophy
 */
class TrophyService
{
	private TrophyCacheServiceInterface $trophyCacheService;
	private TrophyRepository $trophyRepository;

	/**
	 * TrophyService constructor.
	 * @param TrophyCacheServiceInterface $trophyCacheService
	 * @param TrophyRepository $trophyRepository
	 */
	public function __construct(
		TrophyCacheServiceInterface $trophyCacheService,
		TrophyRepository $trophyRepository
	) {
		$this->trophyCacheService = $trophyCacheService;
		$this->trophyRepository = $trophyRepository;
	}

	/**
	 * @param string $id
	 * @return array
	 */
	public function getTrophiesByTeam(string $id): array
	{
		return $this->trophyCacheService->rememberForeverTrophiesByTeam($id, function () use ($id) {
			$trophies = $this->trophyRepository->findByTeamId($id);
			if (!$trophies) {
				throw new NotFoundHttpException();
			}
			$excludedTrophies = [];
			foreach ($trophies as $trophy) {
				/**
				 * @var Trophy $trophy
				 * @var Trophy $excludedTrophies
				 */
				$excludedTrophies[] = $this->trophyRepository->findExcludesByCompetitionTournament(
					$trophy->getCompetitionId(),
					$trophy->getTournamentId(),
					$trophy->getTeamId()
				)[0];
			}
			return array_merge($excludedTrophies, $trophies);
		});
	}

	/**
	 * @param string $id
	 * @return array
	 */
	public function getTrophiesByCompetition(string $id): array
	{
		return $this->trophyCacheService->rememberForeverTrophiesByCompetition($id, function () use ($id) {
			$trophies = $this->trophyRepository->findByCompetition($id);
			if (!$trophies) {
				throw new NotFoundHttpException();
			}
			return $trophies;
		});
	}
}