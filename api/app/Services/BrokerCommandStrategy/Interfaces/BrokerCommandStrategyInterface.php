<?php


namespace App\Services\BrokerCommandStrategy\Interfaces;


use App\ValueObjects\Broker\CommandQuery\Message;


/**
 * Interface BrokerCommandStrategyInterface
 * @package App\Services\BrokerCommandStrategy\Interfaces
 */
interface BrokerCommandStrategyInterface
{
    /**
     * @param Message $message
     * @return mixed
     */
    public function handle(Message $message);
}
