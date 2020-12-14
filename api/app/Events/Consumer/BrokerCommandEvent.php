<?php


namespace App\Events\Consumer;


use App\Events\Consumer\Traits\BrokerEventTrait;
use App\Events\Event;
use App\ValueObjects\Broker\ConsumerEventInterface;


/**
 * Class BrokerCommandEvent
 * @package App\Events\Consumer
 */
class BrokerCommandEvent extends Event implements ConsumerEventInterface
{
    use BrokerEventTrait;
}
