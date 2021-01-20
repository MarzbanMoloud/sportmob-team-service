<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Api\Swagger\Interfaces\OverviewControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OverviewResource;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\TeamsMatch\TeamsMatchService;


/**
 * Class OverviewController
 * @package App\Http\Controllers\Api
 */
class OverviewController extends Controller implements OverviewControllerInterface
{
	private ResponseServiceInterface $responseService;
	private TeamsMatchService $teamsMatchService;

	/**
	 * OverviewController constructor.
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
	public function index(string $team)
	{
		return $this->responseService->createSuccessResponseObject(
			new OverviewResource($this->teamsMatchService->findTeamsMatchByTeamId($team))
		);
	}
}