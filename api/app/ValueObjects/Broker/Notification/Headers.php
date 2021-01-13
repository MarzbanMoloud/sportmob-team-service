<?php


namespace App\ValueObjects\Broker\Notification;


use DateTimeImmutable;


/**
 * Class Headers
 * @package App\ValueObjects\Broker\Notification
 */
class Headers
{
	private string $event;
	private DateTimeImmutable $date;

	/**
	 * @return string
	 */
	public function getEvent(): string
	{
		return $this->event;
	}

	/**
	 * @return DateTimeImmutable
	 */
	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}

	/**
	 * @param string $event
	 * @return Headers
	 */
	public function setEvent(string $event): Headers
	{
		$this->event = $event;
		return $this;
	}

	/**
	 * @param DateTimeImmutable $date
	 * @return Headers
	 */
	public function setDate(DateTimeImmutable $date): Headers
	{
		$this->date = $date;
		return $this;
	}
}