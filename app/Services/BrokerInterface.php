<?php

namespace App\Services;

use App\ValueObjects\Broker\ConsumerEventInterface;

/**
 * Interface BrokerInterface
 * @package App\Services
 */
interface BrokerInterface
{
    /**
     * @param string $broker
     * @return mixed
     */
    public function addBroker(string $broker);

    /**
     * @param string $key
     * @param string $message
     * @return mixed
     */
    public function addMessage(string $key, string $message);

    /**
     * @param string $topic
     * @return mixed
     */
    public function produceMessage(string $topic);

    /**
     * @param string $topic
     * @return mixed
     */
    public function produceMessageByDefault(string $topic);

    /**
     * @param array $topics
     * @param int $timeout
     * @param ConsumerEventInterface $event
     * @param int $limit
     * @return mixed
     */
    public function consumeMessage(array $topics, int $timeout, ConsumerEventInterface $event, int $limit);

    /**
     * @param array $topics
     * @return mixed
     */
    public function consumePureMessage(array $topics);

    /**
     * @return mixed
     */
    public function flushMessages();

}
