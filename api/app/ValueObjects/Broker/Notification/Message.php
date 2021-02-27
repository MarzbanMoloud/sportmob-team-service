<?php


namespace App\ValueObjects\Broker\Notification;


/**
 * Class Message
 * @package App\ValueObjects\Broker\Notification
 */
class Message
{
	private Body $body;
	private Headers $headers;

	/**
	 * @return Body
	 */
	public function getBody(): Body
	{
		return $this->body;
	}

	/**
	 * @param Body $body
	 * @return Message
	 */
	public function setBody(Body $body): Message
	{
		$this->body = $body;
		return $this;
	}

	/**
	 * @return Headers
	 */
	public function getHeaders(): Headers
	{
		return $this->headers;
	}

	/**
	 * @param Headers $headers
	 * @return Message
	 */
	public function setHeaders(Headers $headers): Message
	{
		$this->headers = $headers;
		return $this;
	}
}