<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/28/2020
 * Time: 10:59 AM
 */

namespace App\Services\EventStrategy;


use App\Services\EventStrategy\Interfaces\EventStrategyInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\Message;


/**
 * Class EventContext
 * @package App\Services\EventStrategy
 */
class EventContext implements EventStrategyInterface
{
    private array $strategies = [];

    /**
     * @param $message
     * @return mixed|void
     */
    public function handle(Message $message)
    {
        Event::received($message, $message->getHeaders()->getEvent());

        if ( !isset($this->strategies[$message->getHeaders()->getEvent()]) ) {
            Event::rejected($message, $message->getHeaders()->getEvent(), 'lack of ownership');
            return;
        }

        $eventClass = $this->strategies[$message->getHeaders()->getEvent()];
        app($eventClass)->handle($message->getBody());
    }
}
