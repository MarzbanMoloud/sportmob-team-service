<?php


namespace App\Exceptions;


use Exception;
use Throwable;


/**
 * Class UserActionTransferNotAllow
 * @package App\Exceptions
 */
class UserActionTransferNotAllow extends Exception
{
	/**
	 * UserActionTransferNotAllow constructor.
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}