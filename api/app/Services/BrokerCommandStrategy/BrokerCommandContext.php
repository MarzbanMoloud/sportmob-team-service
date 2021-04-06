<?php


namespace App\Services\BrokerCommandStrategy;


use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandStrategyInterface;
use App\Services\Logger\Answer;
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
		$strategies = [];
        Answer::received($message, $message->getHeaders()->getKey(), $message->getHeaders()->getSource());

        if ( !isset($strategies[$message->getHeaders()->getKey()]) ) {
            Answer::rejected($message, $message->getHeaders()->getKey(), $message->getHeaders()->getSource(), 'lack of ownership');
            return;
        }

        $eventClass = $strategies[$message->getHeaders()->getKey()];
        app($eventClass)->handle($message);
    }
}
