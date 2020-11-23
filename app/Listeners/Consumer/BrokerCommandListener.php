<?php


namespace App\Listeners\Consumer;


use App\Events\Consumer\BrokerCommandEvent;
use App\Services\BrokerCommandStrategy\BrokerCommandContext;
use App\ValueObjects\Broker\CommandQuery\Message;


/**
 * Class BrokerCommandListener
 * @package App\Listeners\Consumer
 */
class BrokerCommandListener
{
    /**
     * @param BrokerCommandEvent $event
     */
    public function handle(BrokerCommandEvent $event)
    {
        $commandQuery = app('Serializer')->deserialize($event->message->getBody(), Message::class, 'json');
        (new BrokerCommandContext())->handle($commandQuery);
    }
}
