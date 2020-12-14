<?php


namespace App\Services\BrokerQueryStrategy\Interfaces;


use App\ValueObjects\Broker\CommandQuery\Message;


/**
 * Interface BrokerCommandStrategyInterface
 * @package App\Services\BrokerCommandStrategy\Interfaces
 */
interface BrokerQueryStrategyInterface
{
    /**
     * @param Message $message
     * @return mixed
     */
    public function handle(Message $message);
}
