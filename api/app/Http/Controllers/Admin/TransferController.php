<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Admin\Swagger\Interfaces\TransferControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerTransferRequest;
use App\Http\Resources\Admin\PlayerTransferResource;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Transfer\TransferService;
use App\ValueObjects\DTO\PlayerTransferDTO;
use Illuminate\Http\Request;


/**
 * Class TransferController
 * @package App\Http\Controllers\Admin
 */
class TransferController extends Controller implements TransferControllerInterface
{
	private ResponseServiceInterface $responseService;
	private TransferService $transferService;

	/**
	 * TransferController constructor.
	 * @param ResponseServiceInterface $responseService
	 * @param TransferService $transferService
	 */
	public function __construct(ResponseServiceInterface $responseService, TransferService $transferService)
	{
		$this->responseService = $responseService;
		$this->transferService = $transferService;
	}

	/**
	 * @param string $player
	 * @return mixed
	 */
	public function index(string $player)
	{
		return $this->responseService->createSuccessResponseObject(
			new PlayerTransferResource($this->transferService->listByPerson($player))
		);
	}

	/**
	 * @param string $transfer
	 * @param Request $request
	 * @return mixed
	 * @throws \App\Exceptions\DynamoDB\DynamoDBException
	 */
	public function update(string $transfer, Request $request)
	{
		(new PlayerTransferRequest())->validation($request);
		$request->request->add(['transferId' => $transfer]);
		$this->transferService->updateItem(new PlayerTransferDTO($request->all()));
		return $this->responseService->createUpdateResponseObject();
	}
}