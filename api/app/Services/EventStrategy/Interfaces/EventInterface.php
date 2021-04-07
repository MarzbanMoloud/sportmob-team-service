<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/28/2020
 * Time: 10:43 AM
 */


namespace App\Services\EventStrategy\Interfaces;


use App\ValueObjects\Broker\Mediator\MessageBody;


/**
 * Interface EventInterface
 * @package App\Events\Interfaces
 */
interface EventInterface
{
    /**
     * @param MessageBody $body
     */
    public function handle(MessageBody $body): void;
}
