<?php


namespace App\Services\BrokerCommandStrategy;


use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandEventInterface;
use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandStrategyInterface;
use App\ValueObjects\Broker\CommandQuery\Message;


/**
 * Class BrokerCommandContext
 * @package App\Services\BrokerCommandStrategy
 */
class BrokerCommandContext implements BrokerCommandStrategyInterface
{
    /**
     * @param Message $message
     * @return mixed|void
     */
    public function handle(Message $message): void
    {
        foreach (app()->tagged(BrokerCommandEventInterface::TAG_NAME) as $event) {
            if ($event->support($message->getHeaders())) {
                $event->handle($message);
                return;
            }
        }
    }
}
