<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Api\Swagger\Interfaces\TransferControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PlayerTransferResource;
use App\Http\Resources\Api\TeamTransferResource;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Transfer\TransferService;
use App\Utilities\Utility;


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
	 * @throws \App\Exceptions\ResourceNotFoundException
	 */
	public function listByTeam(string $team, ?string $season = null)
	{
		$result['transfers'] = $this->transferService->listByTeam($team, $season);
		$result['seasons'] = $this->transferService->getAllSeasons($team);
		return $this->responseService->createSuccessResponseObject(
			new TeamTransferResource($result)
		);
	}

	/**
	 * @param string $player
	 * @return mixed
	 */
	public function listByPlayer(string $player)
	{
		return $this->responseService->createSuccessResponseObject(
			new PlayerTransferResource($this->transferService->listByPlayer($player))
		);
	}

	/**
	 * @param string $action
	 * @param string $user
	 * @param string $transfer
	 * @return mixed
	 * @throws \App\Exceptions\Projection\ProjectionException
	 * @throws \App\Exceptions\UserActionTransferNotAllow
	 */
	public function userActionTransfer(string $action, string $user, string $transfer)
	{
		$this->transferService->userActionTransfer($action, $user, $transfer);
		return $this->responseService->createUpdateResponseObject();
	}
}