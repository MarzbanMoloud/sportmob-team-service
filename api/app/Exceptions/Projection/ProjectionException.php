<?php


namespace App\Exceptions\Projection;


use Exception;
use Throwable;


/**
 * Class ProjectionException
 * @package App\Exceptions\Projection
 */
class ProjectionException extends Exception
{
    /**
     * ProjectionException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
