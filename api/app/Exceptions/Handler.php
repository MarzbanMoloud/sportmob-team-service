<?php

namespace App\Exceptions;

use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Sentry\State\HubInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;


/**
 * Class Handler
 * @package App\Exceptions
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Throwable $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
		app(HubInterface::class)->captureException($exception);
        if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => 'Input data is invalid.',
                'code' => config('common.error_codes.validation_failed')
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Resource not found.',
                'code' => config('common.error_codes.resource_not_found')
            ], Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof DynamoDBRepositoryException) {
            return response()->json([
                'message' => 'unprocessable_request.',
                'code' => config('common.error_codes.unprocessable_request')
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

		if ($exception instanceof ResourceNotFoundException) {
			return response()->json([
				'message' => 'Resource not found.',
				'code' => $exception->getErrorCode()
			], Response::HTTP_NOT_FOUND);
		}

		if ($exception instanceof UserActionTransferNotAllow) {
			return response()->json([
				'message' => 'unprocessable_request.',
				'code' => config('common.error_codes.User_is_not_allowed_to_like')
			], Response::HTTP_UNPROCESSABLE_ENTITY);
		}

        return parent::render($request, $exception);
    }
}
