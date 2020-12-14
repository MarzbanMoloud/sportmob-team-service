<?php
namespace Tests\Traits;

use App\Services\AWS\BrokerService;
use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use Illuminate\Http\Response;

trait AmazonBrokerTrait
{
    /**
     * @var SnsClient
     */
    private SnsClient $snsClient;

    /**
     * @var SqsClient
     */
    private SqsClient $sqsClient;

    private function addClient()
    {
        /**
         * @var BrokerService $Broker
         */
        $Broker = app(BrokerService::class);
        $this->snsClient = $Broker->getSnsClient();
        $this->sqsClient = $Broker->getSqsClient();
    }

    private function deleteTopic(string $name)
    {
        $result = $this->snsClient->deleteTopic([
            'TopicArn' => $name,
        ]);
        return $result->get("@metadata")['statusCode'] == Response::HTTP_OK;
    }

    private function createTopic(string $name)
    {
        $result = $this->snsClient->createTopic([
            'Name' => $name
        ]);
        return $result->get('TopicArn');
    }

    private function listTopics()
    {
        $result = $this->snsClient->listTopics();
        return $result->get('Topics');
    }

    private function listQueues()
    {
        $result = $this->sqsClient->listQueues();
        return $result->get('QueueUrls');
    }

    private function subscribe(string $queue, string $topic)
    {
        $queue = $this->createQueue($queue);
        $topic = $this->createTopic($topic);

        $this->snsClient->subscribe([
            'Attributes' => ['RawMessageDelivery' => 'true'],
            'Protocol' => 'sqs', //sqs
            'Endpoint' => $queue, //http://localhost:443/000000000000/newsQueue
            'ReturnSubscriptionArn' => true,
            'TopicArn' => $topic, //arn:aws:sns:us-west-2:000000000000:event
        ]);
        return ['topic'=> $topic, 'queue'=>$queue];
    }

    private function listSubscriptions()
    {
        $result = $this->snsClient->listSubscriptions();
        return $result->get('Subscriptions');
    }

    private function publishMessage(string $message, string $topic, string $key)
    {
        $result = $this->snsClient->publish([
            'Message' => $message, //string
            'TopicArn' => $topic,
            'MessageDeduplicationId' => md5($message), //unique Id for FIFO - during the 5-minute deduplication interval is treated as a duplicate. - Maximum length 128 alphanumeric characters
            'MessageGroupId' => $key, // - Maximum length 128 alphanumeric characters
            'MessageStructure' => 'json',
        ]);
        return $result->get('MessageId');
    }

    private function receiveMessage(string $queueUrl, int $limit, int $timeout)
    {
        $result = $this->sqsClient->receiveMessage([
            'AttributeNames' => ['SentTimestamp'],
            'MaxNumberOfMessages' => $limit,
            'MessageAttributeNames' => ['All'],
            'QueueUrl' => $queueUrl, // REQUIRED
            'WaitTimeSeconds' => $timeout,
        ]);
        return $result->get('Messages');
    }

    private function deleteQueues(string $name)
    {
        $result = $this->sqsClient->deleteQueue([
            'QueueUrl' => $name
        ]);
        return $result->get("@metadata")['statusCode'] == Response::HTTP_OK;
    }

    private function createQueue(string $queueName)
    {
        $result = $this->sqsClient->createQueue(array(
            'QueueName' => $queueName,
            'Attributes' => array(
                'ReceiveMessageWaitTimeSeconds' => 20
            ),
        ));
        return $result->get('QueueUrl');
    }

    private function flushSNS_SQS()
    {
        $topics = $this->listTopics();
        foreach ($topics as $topic) {
            $this->deleteTopic($topic['TopicArn']);
        }

        $queues = $this->listQueues() ?: [];
        foreach ($queues as $queue) {
            $this->deleteQueues($queue);
        }
    }
}
