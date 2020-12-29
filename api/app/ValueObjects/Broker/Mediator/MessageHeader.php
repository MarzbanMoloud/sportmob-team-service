<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/28/2020
 * Time: 4:37 PM
 */

namespace App\ValueObjects\Broker\Mediator;


/**
 * Class MessageHeader
 * @package App\ValueObjects\Broker\Mediator
 */
class MessageHeader
{

    private string $event;

    private string $priority;

    private \DateTimeImmutable $date;

    /**
     * MessageHeader constructor.
     * @param $event
     * @param $priority
     * @param $date
     */
    public function __construct(string $event, string $priority, \DateTimeImmutable $date)
    {
        $this->event = $event;
        $this->priority = $priority;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getPriority(): string
    {
        return $this->priority;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }
}
