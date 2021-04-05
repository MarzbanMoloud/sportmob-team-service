<?php


namespace App\Services\BrokerCommandStrategy\Interfaces;


use App\ValueObjects\Broker\CommandQuery\Message;


/**
 * Interface BrokerCommandEventInterface
 * @package App\Services\BrokerCommandStrategy\Interfaces
 */
interface BrokerCommandEventInterface
{
    /**
     * @param Message $commandQuery
     */
    public function handle(Message $commandQuery): void;
}
