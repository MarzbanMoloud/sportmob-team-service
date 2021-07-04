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

    private ?string $id;

    /**
     * MessageHeader constructor.
     * @param string $event
     * @param string $priority
     * @param \DateTimeImmutable $date
     * @param string $id
     */
    public function __construct(string $event, string $priority, \DateTimeImmutable $date, ?string $id = null)
    {
        $this->event = $event;
        $this->priority = $priority;
        $this->date = $date;
        $this->id = $id;
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

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }
}
