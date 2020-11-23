<?php


namespace App\ValueObjects\Broker;


/**
 * Class Message
 * @package App\ValueObjects\Broker
 */
class Message
{
    /**
     * @var string
     */
    private string $body;

    /**
     * @var array
     */
    private array $headers = [];

    /**
     * @var array
     */
    private array $extra = [];

    /**
     * BrokerMessage constructor.
     * @param string $body
     * @param array $headers
     * @param array $extra
     */
    public function __construct(string $body, array $headers, array $extra)
    {
        $this->body    = $body;
        $this->headers = $headers;
        $this->extra   = $extra;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getExtra(): array
    {
        return $this->extra;
    }
}
