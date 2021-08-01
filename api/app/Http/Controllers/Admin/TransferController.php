<?php


namespace App\Http\Controllers\Admin;


use App\Exceptions\DynamoDB\DynamoDBException;
use App\Http\Controllers\Admin\Swagger\Interfaces\TransferControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\PersonTransferRequest;
use App\Http\Resources\Admin\PersonTransferResource;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Transfer\TransferService;
use App\ValueObjects\DTO\PersonTransferDTO;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


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
	 * @param string $person
	 * @return mixed
	 */
	public function index(string $person)
	{
		return $this->responseService->createSuccessResponseObject(
			new PersonTransferResource($this->transferService->listByPerson($person))
		);
	}

	/**
	 * @param string $transfer
	 * @param Request $request
	 * @return mixed
	 * @throws DynamoDBException
	 * @throws ValidationException
	 */
	public function update(string $transfer, Request $request)
	{
		(new PersonTransferRequest())->validation($request);
		$request->request->add(['transferId' => $transfer]);
		$this->transferService->updateItem(new PersonTransferDTO($request->all()));
		return $this->responseService->createUpdateResponseObject();
	}
}