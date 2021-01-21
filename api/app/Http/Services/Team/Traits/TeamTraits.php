<?php


namespace App\Http\Services\Team\Traits;


use App\Models\ReadModels\Team;


/**
 * Trait TeamTraits
 * @package App\Http\Services\Team\Traits
 */
trait TeamTraits
{
	/**
	 * @param string $teamId
	 * @return mixed
	 */
	private function findTeam(string $teamId): ?Team
	{
		return $this->teamCacheService->rememberForeverTeam($teamId, function () use ($teamId) {
			return $this->teamRepository->find(['id' => $teamId]);
		});
	}
}