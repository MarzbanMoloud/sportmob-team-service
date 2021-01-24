<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/30/2020
 * Time: 9:31 AM
 */

namespace App\Exceptions\DynamoDB;


use Throwable;
use Exception;


/**
 * Class DynamoDBException
 * @package App\Exceptions\DynamoDB
 */
class DynamoDBException extends Exception
{
	private string $errorCode;

	/**
	 * DynamoDBException constructor.
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
