<?php


namespace App\ValueObjects\Broker\Notification;


/**
 * Class Body
 * @package App\ValueObjects\Broker\Notification
 */
class Body
{
	private array $id = [];
	private array $metadata = [];

	/**
	 * @return array
	 */
	public function getId(): array
	{
		return $this->id;
	}

	/**
	 * @return array
	 */
	public function getMetadata(): array
	{
		return $this->metadata;
	}

	/**
	 * @param array $id
	 * @return Body
	 */
	public function setId(array $id): Body
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @param array $metadata
	 * @return Body
	 */
	public function setMetadata(array $metadata): Body
	{
		$this->metadata = $metadata;
		return $this;
	}
}