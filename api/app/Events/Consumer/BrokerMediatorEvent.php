<?php


namespace App\Events\Consumer;


use App\Events\Consumer\Traits\BrokerEventTrait;
use App\Events\Event;
use App\ValueObjects\Broker\ConsumerEventInterface;


/**
 * Class BrokerMediatorEvent
 * @package App\Events\Consumer
 */
class BrokerMediatorEvent extends Event implements ConsumerEventInterface
{
    use BrokerEventTrait;
}
