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
 * Class DynamoDBRepositoryException
 * @package App\Exceptions\DynamoDB
 */
class DynamoDBRepositoryException extends Exception
{
	/**
	 * DynamoDBRepositoryException constructor.
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}