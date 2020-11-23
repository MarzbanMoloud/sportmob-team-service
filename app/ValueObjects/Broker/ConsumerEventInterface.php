<?php


namespace App\ValueObjects\Broker;



/**
 * Interface ConsumerEventInterface
 * @package App\ValueObjects\Broker
 */
interface ConsumerEventInterface
{
    /**
     * @param Message $message
     * @return void
     */
    public function setMessage(Message $message): void;
}
