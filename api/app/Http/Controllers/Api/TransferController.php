<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Api\Swagger\Interfaces\TransferControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\TransferActionRequest;
use App\Http\Resources\Api\PersonTransferResource;
use App\Http\Resources\Api\TeamTransferResource;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Transfer\TransferService;
use Illuminate\Http\Request;


/**
 * Class TransferController
 * @package App\Http\Controllers\Api
 */
class TransferController extends Controller implements TransferControllerInterface
{
	private TransferService $transferService;
	private ResponseServiceInterface $responseService;

	/**
	 * TransferController constructor.
	 * @param TransferService $transferService
	 * @param ResponseServiceInterface $responseService
	 */
	public function __construct(
		TransferService $transferService,
		ResponseServiceInterface $responseService
	) {
		$this->transferService = $transferService;
		$this->responseService = $responseService;
	}

	/**
	 * @param string $team
	 * @param string|null $season
	 * @return mixed
	 */
	public function listByTeam(string $team, ?string $season = null)
	{
		$result['transfers'] = $this->transferService->listByTeam($team, $season);
		return $this->responseService->createSuccessResponseObject(
			new TeamTransferResource($result)
		);
	}

	/**
	 * @param string $person
	 * @return mixed
	 */
	public function listByPerson(string $person)
	{
		return $this->responseService->createSuccessResponseObject(
			new PersonTransferResource($this->transferService->listByPerson($person))
		);
	}

	/**
	 * @param string $action
	 * @param string $transfer
	 * @param Request $request
	 * @return mixed
	 * @throws \App\Exceptions\Projection\ProjectionException
	 * @throws \App\Exceptions\UserActionTransferNotAllow
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function userActionTransfer(string $action, string $transfer, Request $request)
	{
		(new TransferActionRequest())->validation($request);
		$this->transferService->userActionTransfer($action, $request->userId, $transfer);
		return $this->responseService->createUpdateResponseObject();
	}
}