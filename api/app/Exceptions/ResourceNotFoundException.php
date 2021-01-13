<?php


namespace App\Exceptions;


use Exception;
use Throwable;


/**
 * Class ResourceNotFoundException
 * @package App\Exceptions
 */
class ResourceNotFoundException extends Exception
{
	private string $errorCode;

	/**
	 * ResourceNotFoundException constructor.
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 * @param string $errorCode
	 */
	public function __construct(string $message = "", int $code = 0, Throwable $previous = null, string $errorCode = "")
	{
		$this->errorCode = $errorCode;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return string
	 */
	public function getErrorCode(): string
	{
		return $this->errorCode;
	}
}