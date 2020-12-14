<?php


namespace App\Services\BrokerQueryStrategy;


use App\Services\BrokerQueryStrategy\Interfaces\BrokerQueryEventInterface;
use App\Services\BrokerQueryStrategy\Interfaces\BrokerQueryStrategyInterface;
use App\ValueObjects\Broker\CommandQuery\Message;


/**
 * Class BrokerQueryContext
 * @package App\Services\BrokerQueryStrategy
 */
class BrokerQueryContext implements BrokerQueryStrategyInterface
{
    /**
     * @param Message $message
     * @return mixed|void
     */
    public function handle(Message $message)
    {
        foreach (app()->tagged(BrokerQueryEventInterface::TAG_NAME) as $event) {
            if ($event->support($message)) {
                $event->handle($message);
                return;
            }
        }
    }
}
