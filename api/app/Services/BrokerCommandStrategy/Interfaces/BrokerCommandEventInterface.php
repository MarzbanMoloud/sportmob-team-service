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
     *
     */
    const TAG_NAME = 'BrokerCommandEvent';

    /**
     * @param string $key
     * @return bool
     */
    public function support(string $key): bool;

    /**
     * @param Message $commandQuery
     */
    public function handle(Message $commandQuery): void;
}
