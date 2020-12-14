<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/28/2020
 * Time: 10:32 AM
 */


namespace App\ValueObjects\Broker\Mediator;


/**
 * Class Message
 * @package App\ValueObjects
 */
class Message
{
    /**
     * @var MessageHeader
     */
    private MessageHeader $headers;

    /**
     * @var MessageBody
     */
    private MessageBody $body;

    /**
     * @param MessageHeader $headers
     * @return $this
     */
    public function setHeaders(MessageHeader $headers): Message
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param MessageBody $body
     * @return $this
     */
    public function setBody(MessageBody $body): Message
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return MessageHeader
     */
    public function getHeaders(): MessageHeader
    {
        return $this->headers;
    }

    /**
     * @return MessageBody
     */
    public function getBody(): MessageBody
    {
        return $this->body;
    }
}
