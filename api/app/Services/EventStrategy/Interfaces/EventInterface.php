<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/28/2020
 * Time: 10:43 AM
 */


namespace App\Services\EventStrategy\Interfaces;


use App\ValueObjects\Broker\Mediator\Message;


/**
 * Interface EventInterface
 * @package App\Events\Interfaces
 */
interface EventInterface
{
    /**
     * @param Message $message
     */
    public function handle(Message $message): void;
}
