<?php


namespace App\Services\AWS;


use App\Services\BrokerInterface;
use App\ValueObjects\Broker\Message;
use App\ValueObjects\Broker\ConsumerEventInterface;
use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use RuntimeException;
use Sentry\State\HubInterface;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

class BrokerService implements BrokerInterface
{
    /**
     * @var SnsClient
     */
    private SnsClient $snsClient;

    /**
     * @var SqsClient
     */
    private SqsClient $sqsClient;

    /**
     * @var array
     */
    private array $messages;

    /**
     * @var HubInterface
     */
    private HubInterface $sentryHub;

    /**
     * BrokerService constructor.
     *
     * @param HubInterface $sentryHub
     */
    public function __construct(HubInterface $sentryHub)
    {
        $this->sentryHub = $sentryHub;
        $this->snsClient = new SnsClient(config('aws.sns'));
        $this->sqsClient = new SqsClient(config('aws.sqs'));
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
        Assert::maxLength($key, 128);
        $this->messages[$key][] = $message;
        return $this;
    }

    /**
     * @param string $topic
     * @return bool
     */
    public function produceMessage(string $topic)
    {
        Assert::stringNotEmpty($topic, 'Topic is required.');
        try {
            if (!$this->checkExistTopic($topic)) {
                throw new \InvalidArgumentException('No TopicArn exists.');
            }

            if (empty($this->messages)) {
                throw new \InvalidArgumentException('No message was added yet.');
            }
            foreach ($this->messages as $key => $message) {
                foreach ($message as $msg) {
                    $result = $this->snsClient->publish([
                        'Message' => $msg, //string
                        'TopicArn' => $topic,
                        'MessageDeduplicationId' => md5($msg), //unique Id for FIFO - during the 5-minute deduplication interval is treated as a duplicate. - Maximum length 128 alphanumeric characters
                        'MessageGroupId' => $key, // Maximum length 128 alphanumeric characters
                    ]);
                    $metaData = $result->get('@metadata');
                    if ($metaData['statusCode'] != Response::HTTP_OK) {
                        throw new RuntimeException('Your request has encountered a problem');
                    }
                }
            }
        } catch (\Throwable $exception) {
            throw new RuntimeException($exception->getMessage());
        }

        return true;
    }

    /**
     * @param array $queueUrl
     * @param int $timeout
     * @param ConsumerEventInterface $event
     * @param int $limit
     */
    public function consumeMessage(array $queueUrl, int $timeout, ConsumerEventInterface $event, int $limit)
    {
        $queueUrl = $queueUrl[0];
        $messages = $this->receiveMessage($queueUrl,$timeout,$limit);

        foreach ($messages as $message) {
            $BrokerMessage = new Message($message['Body'], ['MessageId' => $message['MessageId']], $message['Attributes']);
            $event->setMessage($BrokerMessage);
            try {
                event($event);
            } catch (\Throwable $e) {
                $this->sentryHub->captureException($e);
            }

            $this->deleteMessage($queueUrl, $message['ReceiptHandle']);
        }
    }

    /**
     * @param array $queueUrl
     * @param int $timeout
     * @return mixed|void
     */
    public function consumePureMessage(array $queueUrl, int $timeout)
    {
        $messages = [];
        $queueUrl = $queueUrl[0];
        $result = $this->receiveMessage($queueUrl, $timeout);
        foreach ($result as $message) {
            $messages[] = $message['Body'];
            $this->deleteMessage($queueUrl, $message['ReceiptHandle']);
        }
        return $messages;
    }

    /**
     * @return $this
     */
    public function flushMessages()
    {
        $this->messages = [];
        return $this;
    }

    /**
     * @param string $broker
     * @return $this
     */
    public function addBroker(string $broker)
    {
        return $this;
    }

    /**
     * @param string $topic
     */
    public function produceMessageByDefault(string $topic)
    {
    }

    /**
     * @param string $topicArn
     * @return bool
     */
    private function checkExistTopic(string $topicArn)
    {
        $result = $this->snsClient->listTopics();
        $topics = $result->get('Topics');
        foreach ($topics as $topic) {
            if ($topic['TopicArn'] == $topicArn) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $queueUrl
     * @return bool
     */
    private function checkExistQueue(string $queueUrl)
    {
        $result = $this->sqsClient->listQueues();
        $queues = $result->get('QueueUrls');

        return in_array($queueUrl, $queues);
    }

    /**
     * @param string $queueUrl
     * @param string $receiptHandle
     */
    private function deleteMessage(string $queueUrl, string $receiptHandle){
        try {
            $this->sqsClient->deleteMessage([
                'QueueUrl' => $queueUrl, // REQUIRED
                'ReceiptHandle' => $receiptHandle // REQUIRED
            ]);
        } catch (\Throwable $e) {
            $this->sentryHub->captureException($e);
        }
    }

    /**
     * @param string $queueUrl
     * @param int $limit
     * @param int $timeout
     * @return mixed|void
     */
    private function receiveMessage(string $queueUrl, $timeout=20, $limit=10)
    {
        Assert::stringNotEmpty($queueUrl, 'Queue url is required.');
        try {
            if (!$this->checkExistQueue($queueUrl)) {
                throw new \InvalidArgumentException('No queueUrl exists.');
            }

            $result = $this->sqsClient->receiveMessage([
                'AttributeNames' => ['SentTimestamp'],
                'MessageAttributeNames' => ['All'],
                'MaxNumberOfMessages' => $limit, //Valid values: 1 to 10. Default: 1.
                'QueueUrl' => $queueUrl, // REQUIRED
                'VisibilityTimeout' => config('broker.visibility_timeout_message', 20), //The duration (in seconds) that the received messages are hidden from subsequent retrieve requests after being retrieved by a ReceiveMessage request.
                'WaitTimeSeconds' => $timeout, //The duration (in seconds) for which the call waits for a message to arrive in the queue before returning. - Valid values: 0 to 20
            ]);
            $messages = $result->get('Messages');
            $metaData = $result->get('@metadata');

            if ($metaData['statusCode'] != Response::HTTP_OK) {
                throw new RuntimeException('Your request has encountered a problem');
            }

            if (empty($messages)) {
                return;
            }
        } catch (\Throwable $exception) {
            throw new RuntimeException($exception->getMessage());
        }

        return $messages;
    }

    /**
     * @return SnsClient
     */
    public function getSnsClient(): SnsClient
    {
        return $this->snsClient;
    }

    /**
     * @return SqsClient
     */
    public function getSqsClient(): SqsClient
    {
        return $this->sqsClient;
    }


}
