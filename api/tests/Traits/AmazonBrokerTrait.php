<?php

namespace Tests\Traits;

use App\Services\AWS\BrokerService;
use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;

trait AmazonBrokerTrait
{
    private BrokerService $brokerService;
    private SnsClient $snsClient;
    private SqsClient $sqsClient;
    private static array $TopicArn;
    private static array $QueueArn;
    private static array $QueueUrl;

    private function setupAWSBroker()
    {
        $this->brokerService = app(BrokerService::class);
        $this->snsClient = $this->brokerService->getSnsClient();
        $this->sqsClient = $this->brokerService->getSqsClient();
        $this->initAWSBroker();
    }

    private static function getQueuePolicy($queueName)
    {
        $QueueArn = sprintf("arn:aws:sqs:%s:%s:%s",
            config('aws.sqs.region'),
            config('aws.account'),
            $queueName);
        return [
            'Version' => '2012-10-17',
            'Id' => sprintf("%s/SQSDefaultPolicy", $QueueArn),
            'Statement' => [
                'Effect' => 'Allow',
                'Principal' => ['Service' => 'sns.amazonaws.com'],
                'Action' => [
                    'sqs:SendMessage',
                    'sqs:ReceiveMessage'
                ],
                'Resource' => $QueueArn
            ]
        ];
    }

    private function initAWSBroker()
    {
        list($ExistTopics, $ExistQueues) = $this->setupTopicsAndQueues();

        if ($topics = array_diff_key (config('broker.topics'), $ExistTopics)) {
            foreach ($topics as $key => $topic) {
                $Topic =
                    $this->snsClient->createTopic([
                        'Name' => sprintf("%s.fifo", $topic),
                        'Attributes' => [
                            'FifoTopic' => 'true',
                            'ContentBasedDeduplication' => 'false',
                        ]
                    ]);
                config([sprintf("broker.topics.%s", $key) => $Topic->get('TopicArn')]);
            }
        }

        if ($queues = array_diff_key(config('broker.queues'), $ExistQueues)) {
            foreach ($queues as $key => $queue) {
                $Queue = $this->sqsClient->createQueue([
                    'QueueName' => sprintf("%s.fifo", $queue),
                    'Attributes' => [
                        'FifoQueue' => 'true',
                        'ContentBasedDeduplication' => 'false',
                        'Policy' => json_encode(self::getQueuePolicy(sprintf("%s.fifo", $queue)))
                    ]
                ]);
                $QueueAttr[$key] =
                    $this->sqsClient->getQueueAttributes([
                        'QueueUrl' => $Queue->get('QueueUrl'),
                        'AttributeNames' => ['QueueArn']
                    ]);

                config([sprintf("broker.queues.%s", $key) => $Queue->get('QueueUrl')]);

                foreach (config('broker.topics') as $topicKey => $value) {
                    if (strpos($topicKey, $key) === 0) {
                        $this->snsClient->subscribe([
                            'Attributes' => ['RawMessageDelivery' => 'true'],
                            'Endpoint' => $QueueAttr[$key]->get('Attributes')['QueueArn'],
                            'Protocol' => 'sqs',
                            'TopicArn' => $value
                        ]);
                    }
                }
            }
        }
    }

    private function setupTopicsAndQueues()
    {
        $ExistTopics = [];
        $ExistQueues = [];
        foreach (array_map(function ($topic) {
            return $topic['TopicArn'];
        },
            $this->snsClient->listTopics()->get('Topics')) as $topicArn) {
            foreach (config('broker.topics') as $key => $topic) {
                if (strstr($topicArn, $topic)) {
                    $ExistTopics[$key] = $topicArn;
                }
            }
        }
        $queues = $this->sqsClient->listQueues()->get('QueueUrls') ?: [];
        foreach ($queues as $queueUrl) {
            foreach (config('broker.queues') as $key => $queue) {
                if (strstr($queueUrl, $queue)) {
                    $ExistQueues[$key] = $queueUrl;
                }
            }
        }
        foreach ($ExistTopics as $key => $topic) {
            config([sprintf("broker.topics.%s", $key) => $topic]);
        }
        foreach ($ExistQueues as $key => $queueUrl) {
            config([sprintf("broker.queues.%s", $key) => $queueUrl]);
        }

        return [$ExistTopics, $ExistQueues];
    }

    private function tearDownAwsBroker()
    {
        $this->setupTopicsAndQueues();
        foreach (config('broker.queues') as $queue) {
            $this->sqsClient->purgeQueue([
                'QueueUrl' => $queue
            ]);

            $this->sqsClient->deleteQueue([
                'QueueUrl' => $queue
            ]);
        }

        foreach (config('broker.topics') as $topic) {
            $this->removeTopicByTopicName($topic);
        }

        $this->unsubscription();
    }

    /**
     * @param string $topicName
     * @param string $queueName
     */
    private function removeBrokerByTopicNameByQueueName(string $topicName, string $queueName)
    {
        $this->removeSubscriptionByTopicName($topicName);
        $this->removeTopicByTopicName($topicName);
        $this->removeQueueByQueueName($queueName);
    }

    /**
     * @param string $queueName
     */
    public function removeQueueByQueueName(string $queueName)
    {
        $this->sqsClient->deleteQueue([
            'QueueUrl' => $queueName
        ]);
    }

    /**
     * @param string $topicName
     */
    private function removeTopicByTopicName(string $topicName)
    {
        $this->snsClient->deleteTopic([
            'TopicArn' => $topicName
        ]);
    }

    /**
     * @param string $topicName
     */
    private function removeSubscriptionByTopicName(string $topicName)
    {
        $subscriptions = $this->snsClient->listSubscriptions()->get('Subscriptions') ?: [];
        foreach ($subscriptions as $subscription) {
            if ($subscription['TopicArn'] === $topicName) {
                $this->snsClient->unsubscribe([
                    "SubscriptionArn" => $subscription['SubscriptionArn']
                ]);
            }
        }
    }

    private function unsubscription()
    {
        $nextToken = null;
        do {
            $subscriptions = $this->snsClient->listSubscriptions([
                'NextToken' => $nextToken
            ]);
            foreach ($subscriptions->get('Subscriptions') ?: [] as $subscription) {
                if (in_array($subscription['TopicArn'], config('broker.topics'))) {
                    $this->snsClient->unsubscribe([
                        "SubscriptionArn" => $subscription['SubscriptionArn']
                    ]);
                }
            }
            $nextToken = $subscriptions->get('NextToken');
        } while ($nextToken);
    }
}
