<?php


namespace App\Events\Consumer\Traits;


use App\ValueObjects\Broker\Message;


/**
 * Trait BrokerEventTrait
 * @package App\Events\Consumer\Traits
 */
trait BrokerEventTrait
{
    /**
     * @var Message
     */
    public Message $message;

    /**
     * @inheritDoc
     */
    public function setMessage(Message $message): void
    {
        $this->message = $message;
    }
}
