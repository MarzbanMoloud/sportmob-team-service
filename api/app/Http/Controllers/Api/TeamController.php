<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Api\Swagger\Interfaces\TeamControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FavoriteResource;
use App\Http\Resources\Api\OverviewResource;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\TeamsMatch\TeamsMatchService;


/**
 * Class TeamController
 * @package App\Http\Controllers\Api
 */
class TeamController extends Controller implements TeamControllerInterface
{
	private ResponseServiceInterface $responseService;
	private TeamsMatchService $teamsMatchService;

	/**
	 * TeamController constructor.
	 * @param ResponseServiceInterface $responseService
	 * @param TeamsMatchService $teamsMatchService
	 */
	public function __construct(
		ResponseServiceInterface $responseService,
		TeamsMatchService $teamsMatchService
	) {
		$this->responseService = $responseService;
		$this->teamsMatchService = $teamsMatchService;
	}

	/**
	 * @param string $team
	 * @return mixed
	 */
	public function overview(string $team)
	{
		return $this->responseService->createSuccessResponseObject(
			new OverviewResource($this->teamsMatchService->getTeamsMatchInfo($team))
		);
	}

	/**
	 * @param string $team
	 * @return mixed
	 */
	public function favorite(string $team)
	{
		return $this->responseService->createSuccessResponseObject(
			new FavoriteResource($this->teamsMatchService->getTeamsMatchInfo($team))
		);
	}
}