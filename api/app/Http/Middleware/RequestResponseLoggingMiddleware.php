<?php

namespace App\Http\Middleware;

use Closure;
use Laravel\Lumen\Http\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RequestResponseLoggingMiddleware
{

    private SerializerInterface $serializer;
    private LoggerInterface $logger;

    /**
     * RequestResponseLoggingMiddleware constructor.
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->serializer = $serializer;
        $this->logger     = $logger;
    }

    public function handle(Request $request, Closure $next)
    {
        $response       = $next( $request );
        $requestContext =
            [
                'method'     => $request->method(),
                'requestUri' => $request->getUri(),
                'baseUrl'    => $request->getBaseUrl(),
                'pathInfo'   => $request->getPathInfo(),
                'headers'    => $request->headers->all(),
                'request'    => $request->request->all(),
                'attributes' => $request->attributes->all(),
                'query'      => $request->query->all(),
                'files'      => $request->files->all(),
                'content'    => (string)$request->getContent(),
            ];

        try {
            $this->logger->alert( 'Request/Response',
                                  [
                                      'request'  => $requestContext,
                                      'response' => $this->serializer->normalize( $response, 'array' )
                                  ] );
        } catch (\Exception $e) {
        }

        return $response;
    }

}