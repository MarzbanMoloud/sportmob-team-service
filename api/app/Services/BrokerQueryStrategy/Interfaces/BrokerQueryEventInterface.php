<?php


namespace App\Services\BrokerQueryStrategy\Interfaces;


use App\ValueObjects\Broker\CommandQuery\Message;


/**
 * Interface BrokerQueryEventInterface
 * @package App\Services\BrokerQueryStrategy\Interfaces
 */
interface BrokerQueryEventInterface
{
    /**
     * @param Message $commandQuery
     */
    public function handle(Message $commandQuery): void;
}
