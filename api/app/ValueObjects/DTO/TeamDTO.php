<?php


namespace App\ValueObjects\DTO;


/**
 * Class TeamDTO
 * @package App\ValueObjects\DTO
 */
class TeamDTO
{
	private array $request;

	/**
	 * TeamDTO constructor.
	 * @param array $request
	 */
	public function __construct(array $request)
	{
		$this->request = $request;
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->request['id'];
	}

	/**
	 * @return string
	 */
	public function getOriginalName(): string
	{
		return $this->request['name']['original'];
	}

	/**
	 * @return string
	 */
	public function getOfficialName(): string
	{
		return $this->request['name']['official'];
	}

	/**
	 * @return string
	 */
	public function getShortName(): string
	{
		return $this->request['name']['short'];
	}
}