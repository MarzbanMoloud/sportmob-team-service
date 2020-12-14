<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/28/2020
 * Time: 10:59 AM
 */

namespace App\Services\EventStrategy;


use App\Services\EventStrategy\Interfaces\EventInterface;
use App\Services\EventStrategy\interfaces\EventStrategyInterface;
use App\ValueObjects\Broker\Mediator\Message;


/**
 * Class EventContext
 * @package App\Services\EventStrategy
 */
class EventContext implements EventStrategyInterface
{
    /**
     * @param $message
     * @return mixed|void
     */
    public function handle(Message $message)
    {
        foreach (app()->tagged(EventInterface::TAG_NAME) as $event) {
            if ($event->support($message)) {
                $event->handle($message->getBody());
                return;
            }
        }
    }
}
