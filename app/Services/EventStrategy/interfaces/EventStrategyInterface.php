<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/28/2020
 * Time: 11:08 AM
 */


namespace App\Services\EventStrategy\Interfaces;


use App\ValueObjects\Broker\Mediator\Message;


/**
 * Interface EventContextInterface
 * @package App\Services\EventStrategy\interfaces
 */
interface EventStrategyInterface
{
    /**
     * @param Message $messageValueObject
     * @return mixed
     */
    public function handle(Message $messageValueObject);
}
