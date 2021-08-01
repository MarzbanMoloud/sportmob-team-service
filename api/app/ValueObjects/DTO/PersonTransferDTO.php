<?php


namespace App\ValueObjects\DTO;


/**
 * Class PersonTransferDTO
 * @package App\ValueObjects\DTO
 */
class PersonTransferDTO
{
	private array $request;

	/**
	 * PlayerTransferDTO constructor.
	 * @param array $request
	 */
	public function __construct(array $request)
	{
		$this->request = $request;
	}

	/**
	 * @return mixed
	 */
	public function getTransferId()
	{
		return $this->request['transferId'];
	}

	/**
	 * @return mixed
	 */
	public function getAnnouncedDate()
	{
		return $this->request['announcedDate'];
	}

	/**
	 * @return mixed
	 */
	public function getContractDate()
	{
		return $this->request['contractDate'];
	}

	/**
	 * @return mixed
	 */
	public function getMarketValue()
	{
		return $this->request['marketValue'];
	}
}