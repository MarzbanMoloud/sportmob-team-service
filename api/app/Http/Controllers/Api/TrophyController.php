<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Api\Swagger\Interfaces\TrophyControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TrophiesByCompetitionResource;
use App\Http\Resources\Api\TrophiesByTeamResource;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Trophy\TrophyService;


/**
 * Class TrophyController
 * @package App\Http\Controllers\Api
 */
class TrophyController extends Controller implements TrophyControllerInterface
{
	private ResponseServiceInterface $responseService;
	private TrophyService $trophyService;

	/**
	 * TrophyController constructor.
	 * @param ResponseServiceInterface $responseService
	 * @param TrophyService $trophyService
	 */
	public function __construct(
		ResponseServiceInterface $responseService,
		TrophyService $trophyService
	) {
		$this->responseService = $responseService;
		$this->trophyService = $trophyService;
	}

	/**
	 * @param string $team
	 * @return mixed
	 */
	public function trophiesByTeam(string $team)
	{
		$trophies = $this->trophyService->getTrophiesByTeam($team);
		return $this->responseService->createSuccessResponseObject(
			(new TrophiesByTeamResource($trophies))
		);
	}

	/**
	 * @param string $competition
	 * @return mixed
	 */
	public function trophiesByCompetition(string $competition)
	{
		$trophies = $this->trophyService->getTrophiesByCompetition($competition);
		return $this->responseService->createSuccessResponseObject(
			new TrophiesByCompetitionResource($trophies)
		);
	}
}