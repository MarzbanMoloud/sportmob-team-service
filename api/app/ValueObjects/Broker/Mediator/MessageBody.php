<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/28/2020
 * Time: 4:39 PM
 */

namespace App\ValueObjects\Broker\Mediator;


/**
 * Class MessageBody
 * @package App\ValueObjects\Broker
 */
class MessageBody
{
    /**
     * @var array
     */
    private array $identifiers = [];

    /**
     * @var array
     */
    private array $metadata = [];

    /**
     * MessageBody constructor.
     * @param array $identifiers
     * @param array $metadata
     */
    public function __construct(array $identifiers, array $metadata)
    {
        $this->identifiers = $identifiers;
        $this->metadata = $metadata;
    }

    /**
     * @return array
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
