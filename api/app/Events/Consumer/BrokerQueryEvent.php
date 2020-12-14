<?php


namespace App\Events\Consumer;


use App\Events\Consumer\Traits\BrokerEventTrait;
use App\Events\Event;
use App\ValueObjects\Broker\ConsumerEventInterface;


/**
 * Class BrokerQueryEvent
 * @package App\Events\Consumer
 */
class BrokerQueryEvent extends Event implements ConsumerEventInterface
{
    use BrokerEventTrait;
}
