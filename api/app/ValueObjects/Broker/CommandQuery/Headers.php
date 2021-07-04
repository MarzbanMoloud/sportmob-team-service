<?php


namespace App\ValueObjects\Broker\CommandQuery;


/**
 * Class Headers
 * @package App\ValueObjects\Broker\Query
 */
class Headers
{
    /**
     * @var string
     */
    private string $source;

    /**
     * @var string
     */
    private string $destination;

    /**
     * @var string
     */
    private string $key;

    /**
     * @var string
     */
    private string $id;

    /**
     * @var string
     */
    private string $eventId;

    /**
     * @var string
     */
    private string $date;

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param $source
     * @return $this
     */
    public function setSource(string $source): Headers
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @param $destination
     * @return $this
     */
    public function setDestination(string $destination): Headers
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param $key
     * @return $this
     */
    public function setKey(string $key): Headers
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getEventId(): string
    {
        return $this->eventId;
    }

    /**
     * @param string $eventId
     * @return Headers
     */
    public function setEventId(string $eventId): Headers
    {
        $this->eventId = $eventId;
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId(string $id): Headers
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param $date
     * @return $this
     */
    public function setDate(string $date): Headers
    {
        $this->date = $date;
        return $this;
    }
}
