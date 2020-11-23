<?php

namespace App\Services\Kafka;

use App\Services\BrokerInterface;
use App\ValueObjects\Broker\ConsumerEventInterface;
use App\ValueObjects\Broker\Message as BrokerMessage;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use RdKafka\Producer;
use Sentry\State\HubInterface;
use Webmozart\Assert\Assert;

/**
 * Class KafkaService
 * @package App\Services\Kafka
 */
class KafkaService implements BrokerInterface
{
    private Conf $producerConfig;

    private Conf $consumerConfig;

    private HubInterface $sentryHub;

    private array $brokers;

    private array $messages;

    /**
     * KafkaService constructor.
     * @param HubInterface $sentryHub
     */
    public function __construct(HubInterface $sentryHub)
    {
        $this->producerConfig = new Conf();
        $this->consumerConfig = new Conf();
        $this->sentryHub = $sentryHub;

        $this->producerConfig->set('client.id', config('broker.kafka.producer.clientId'));
        // Maximum time a broker socket operation may block. A lower value improves responsiveness at the expense of slightly higher CPU usage.
        $this->producerConfig->set('socket.blocking.max.ms', config('broker.kafka.producer.socketBlockingMaxMs'));
        // Local message timeout. This value is only enforced locally and limits the time a produced message waits for successful delivery. A time of 0 is infinite.
        $this->producerConfig->set('message.timeout.ms', config('broker.kafka.producer.messageTimeoutMs'));
        // Delivery report callback
        $this->producerConfig->setDrMsgCb(static function (Producer $producer, Message $message) {
            if ($message->err) {
                throw new \RuntimeException($message->key, rd_kafka_err2str($message->err));
            }
        });

        // Client group id string. All clients sharing the same group.id belong to the same group.
        $this->consumerConfig->set('group.id', config('broker.kafka.consumer.groupId'));
        // Offset commit store method: 'file' - local file store (offset.store.path, et.al), 'broker' - broker commit store (requires Apache Kafka 0.8.2 or later on the broker).
        $this->consumerConfig->set('offset.store.method', config('broker.kafka.consumer.offsetStoreMethod'));
        // Action to take when there is no initial offset in offset store or the desired offset is out of range: 'smallest','earliest','largest','latest','error'
        // earliest :  automatically reset the offset to the smallest offset
        // latest :  automatically reset the offset to the largest offset
        // error :  trigger an error which is retrieved by consuming messages and checking 'message->err'
        $this->consumerConfig->set('auto.offset.reset', config('broker.kafka.consumer.autoOffsetReset'));
        $this->consumerConfig->setRebalanceCb(static function (
            KafkaConsumer $consumer,
            $error,
            array $partitions = null
        ) {
            switch ($error) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    /**
                     * @todo Log Assign Partition
                     */
                    $consumer->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    /**
                     * @todo Log Revoke Partition
                     */
                    $consumer->assign(null);
                    break;

                default:
                    throw new \Exception($error);
            }
        });
    }

    /**
     * @param string $broker
     * @return $this
     */
    public function addBroker(string $broker)
    {
        Assert::stringNotEmpty($broker, 'Broker is required.');
        Assert::regex($broker, '/[a-zA-Z0-9\._\-]*\:[0-9]{2,4}/', 'Invalid broker.');
        $this->brokers[] = $broker;
        $this->consumerConfig->set('metadata.broker.list', join(",", $this->brokers));
        return $this;
    }

    /**
     * @param string $key
     * @param string $message
     * @return $this
     */
    public function addMessage(string $key, string $message)
    {
        Assert::stringNotEmpty($key);
        Assert::stringNotEmpty($message);
        $this->messages[$key][] = $message;
        return $this;
    }

    /**
     * @param string $topic
     * @return bool
     */
    public function produceMessage(string $topic)
    {
        if (empty($this->brokers)) {
            throw new \InvalidArgumentException('No broker exists.');
        }
        if (empty($this->messages)) {
            throw new \InvalidArgumentException('No message was added yet.');
        }
        $Result   = RD_KAFKA_RESP_ERR_NO_ERROR;
        $Producer = new Producer($this->producerConfig);
        $Producer->addBrokers(join( ",", $this->brokers));
        $Topic = $Producer->newTopic($topic);
        foreach ($this->messages as $key => $messages) {
            foreach ($messages as $message) {
                $Topic->produce(RD_KAFKA_PARTITION_UA, 0, $message, $key);
            }
        }
        //If application is long-lived you should call poll() at regular intervals to serve any delivery report callbacks you've registered.
        $Producer->poll(config('broker.kafka.producer.pollTimeout') * 1000);
        for ($Retry = 0; $Retry < 10; $Retry++) {
            $Result = $Producer->flush(10000);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $Result) {
                break;
            }
        }

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $Result) {
            throw new \RuntimeException('Was unable to flush, messages might be lost!');
        }
        return $Result === RD_KAFKA_RESP_ERR_NO_ERROR;
    }

    /**
     * @param array $topics
     * @param int $timeout
     * @param ConsumerEventInterface $event
     * @param int $limit
     * @throws \RdKafka\Exception
     */
    public function consumeMessage(array $topics, int $timeout, ConsumerEventInterface $event, int $limit)
    {
        if (empty($this->brokers)) {
            throw new \InvalidArgumentException('No broker exists');
        }
        $Message       = null;
        $StopConsuming = false;
        $Consumer      = new KafkaConsumer($this->consumerConfig);
        $Consumer->subscribe($topics);
        $Counter = 0;
        while ($StopConsuming === false) {
            $Message = $Consumer->consume($timeout * 1000);
            if (!in_array($Message->topic_name, $topics)) {
                break;
            }
            if (!$Message instanceof Message) {
                throw new \RuntimeException(sprintf("Invalid Message"));
            }
            switch ($Message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $BrokerMessage =
                        new BrokerMessage( $Message->payload,
                            $Message->headers ?? [],
                            [
                                'topic' => $Message->topic_name,
                                'offset' => $Message->offset
                            ] );
                    $event->setMessage( $BrokerMessage );
                    try {
                        event($event);
                    } catch (\Throwable $e) {
                        $this->sentryHub->captureException($e);
                    }
                    $Consumer->commit( $Message );
                    $Counter++;
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    //TODO:: Log "No more messages; will wait for more"
                    $StopConsuming = true;
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    //TODO:: Log "Timed out"
                    $StopConsuming = true;
                    break;
                default:
                    throw new \RuntimeException($Message->errstr(), $Message->err);
                    break;
            }
            if ($Counter > $limit - 1) {
                $StopConsuming = true;
            }
        }
    }

    /**
     * @param string $topic
     */
    public function produceMessageByDefault(string $topic)
    {
        $this->addBroker(config('broker.host'));
        $this->produceMessage($topic);
    }

    /**
     * @return array
     */
    public function getBrokers(): array
    {
        return $this->brokers;
    }

    /**
     * @return Conf
     */
    public function getProducerConfig(): Conf
    {
        return $this->producerConfig;
    }

    /**
     * @return Conf
     */
    public function getConsumerConfig(): Conf
    {
        return $this->consumerConfig;
    }

    public function flushMessages()
    {
        $this->messages = [];
        return $this;
    }

    /**
     * @param array $topics
     * @param int $timeout
     * @return array
     */
    public function consumePureMessage(array $topics, $timeout = 60)
    {
        if (empty( $this->brokers )) {
            throw new \InvalidArgumentException( 'No broker exists' );
        }
        $Message       = null;
        $Messages      = [];
        $StopConsuming = false;
        $Consumer      = new KafkaConsumer( $this->consumerConfig );
        $Consumer->subscribe( $topics );
        while ($StopConsuming === false) {
            $Message = $Consumer->consume( $timeout * 1000 );
            if (!in_array( $Message->topic_name, $topics )) {
                break;
            }
            if (!$Message instanceof Message) {
                throw new \RuntimeException( sprintf( "Invalid Message" ) );
            }
            switch ($Message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $Messages[] = $Message;
                    $Consumer->commit( $Message );
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    $StopConsuming = true;
                    break;
                default:
                    throw new \RuntimeException( $Message->errstr(), $Message->err );
                    break;
            }
        }
        return $Messages;
    }
}
