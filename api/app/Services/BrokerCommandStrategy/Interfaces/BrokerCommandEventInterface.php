<?php


namespace App\Services\BrokerCommandStrategy\Interfaces;


use App\ValueObjects\Broker\CommandQuery\Headers;
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
	 * @param Headers $headers
	 * @return bool
	 */
    public function support(Headers $headers): bool;

    /**
     * @param Message $commandQuery
     */
    public function handle(Message $commandQuery): void;
}
