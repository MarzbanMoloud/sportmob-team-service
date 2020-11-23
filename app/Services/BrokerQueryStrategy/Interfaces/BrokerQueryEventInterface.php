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
     *
     */
    const TAG_NAME = 'BrokerQueryEvent';

    /**
     * @param Message $commandQuery
     * @return bool
     */
    public function support(Message $commandQuery): bool;

    /**
     * @param Message $commandQuery
     */
    public function handle(Message $commandQuery): void;
}
