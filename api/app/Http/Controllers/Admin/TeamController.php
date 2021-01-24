<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Admin\Swagger\Interfaces\TeamControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeamUpdateRequest;
use App\Http\Resources\Admin\TeamResource;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Team\TeamService;
use App\ValueObjects\DTO\TeamDTO;
use Illuminate\Http\Request;


/**
 * Class TeamController
 * @package App\Http\Controllers\Admin
 */
class TeamController extends Controller implements TeamControllerInterface
{
	private ResponseServiceInterface $responseService;
	private TeamService $teamService;

	/**
	 * TeamController constructor.
	 * @param ResponseServiceInterface $responseService
	 * @param TeamService $teamService
	 */
	public function __construct(
		ResponseServiceInterface $responseService,
	    TeamService $teamService
	) {
		$this->responseService = $responseService;
		$this->teamService = $teamService;
	}

	/**
	 * @param string $team
	 * @return mixed
	 */
	public function show(string $team)
	{
		return $this->responseService->createSuccessResponseObject(
			new TeamResource($this->teamService->findTeamById($team))
		);
	}

	/**
	 * @param string $team
	 * @param Request $request
	 * @return mixed
	 * @throws \App\Exceptions\DynamoDB\DynamoDBException
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update(string $team, Request $request)
	{
		(new TeamUpdateRequest())->validation($request);
		$request->request->add(['id' => $team]);
		$this->teamService->updateTeam(new TeamDTO($request->all()));
		return $this->responseService->createUpdateResponseObject();
	}
}