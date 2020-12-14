<?php


namespace App\ValueObjects\Broker\CommandQuery;


/**
 * Class Message
 * @package App\ValueObjects\Broker\Query
 */
class Message
{
    /**
     * @var Headers
     */
    private Headers $headers;

    /**
     * @var array
     */
    private array $body;

    /**
     * @return mixed
     */
    public function getHeaders(): Headers
    {
        return $this->headers;
    }

    /**
     * @param Headers $headers
     * @return $this
     */
    public function setHeaders(Headers $headers): Message
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @param array $body
     * @return $this
     */
    public function setBody(array $body): Message
    {
        $this->body = $body;
        return $this;
    }
}
