<?php


namespace App\Services\BrokerQueryStrategy;


use App\Services\BrokerQueryStrategy\Interfaces\BrokerQueryStrategyInterface;
use App\Services\Logger\Question;
use App\ValueObjects\Broker\CommandQuery\Message;


/**
 * Class BrokerQueryContext
 * @package App\Services\BrokerQueryStrategy
 */
class BrokerQueryContext implements BrokerQueryStrategyInterface
{
    private array $strategies = [];

    /**
     * @param Message $message
     * @return mixed|void
     */
    public function handle(Message $message)
    {
        Question::received($message, $message->getHeaders()->getKey(), $message->getHeaders()->getSource());

        if ( !isset($this->strategies[$message->getBody()['entity']]) ) {
            Question::rejected($message, $message->getHeaders()->getKey(), $message->getHeaders()->getSource(), 'lack of ownership');
            return;
        }

        $eventClass = $this->strategies[$message->getBody()['entity']];
        app($eventClass)->handle($message);
    }
}
