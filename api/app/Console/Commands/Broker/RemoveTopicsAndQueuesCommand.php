<?php
/**
 * Class RemoveTopicsAndQueuesCommand
 * @author Paria Taghioon <p.taghioon@tgbsco.com>
 * @package App\Console\Commands\Broker
 * Date: 3/30/2021
 * Time: 1:28 PM
 */

namespace App\Console\Commands\Broker;


use App\Services\AWS\BrokerService;
use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use Illuminate\Console\Command;

class RemoveTopicsAndQueuesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'broker:remove:topics:queues';

    /**
     * @var string
     */
    protected $description = 'Unsubscribe ,delete topics and queues and purge message';

    /**
     * @var BrokerService
     */
    private BrokerService $brokerService;

    /**
     * @var SnsClient
     */
    private SnsClient $snsClient;

    /**
     * @var SqsClient
     */
    private SqsClient $sqsClient;

    /**
     * RemoveTopicsAndQueuesCommand constructor.
     * @param BrokerService $brokerService
     */
    public function __construct(BrokerService $brokerService)
    {
        parent::__construct();
        $this->brokerService = $brokerService;
        $this->snsClient = $this->brokerService->getSnsClient();
        $this->sqsClient = $this->brokerService->getSqsClient();
    }

    public function handle()
    {
        $this->deleteTopics();
        $this->alert('Topics deleted successfully.');

        $this->deleteQueues();
        $this->alert('Queues deleted successfully.');

        $this->unsubscription();
        $this->alert('Subscriptions were successfully unsubscribed.');
    }

    private function unsubscription(): void
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

    private function deleteQueues(): void
    {
        $nextToken = [];
        do {
            $queues = $this->sqsClient->listQueues($nextToken);

            foreach ($queues->get('QueueUrls') ?: [] as $queueUrl) {
                if (in_array($queueUrl, config('broker.queues'))) {
                    $this->sqsClient->purgeQueue(['QueueUrl' => $queueUrl]);
                    $this->sqsClient->deleteQueue(['QueueUrl' => $queueUrl]);
                }
            }
            $nextToken = $queues->get('NextToken') ? ['NextToken' => $queues->get('NextToken')] : null;
        } while ($nextToken);
    }

    private function deleteTopics(): void
    {
        $nextToken = null;
        do {
            $result = $this->snsClient->listTopics([
                'NextToken' => $nextToken
            ]);

            foreach ($result->get('Topics') ?: [] as $topicArn) {
                if (in_array($topicArn['TopicArn'], config('broker.topics'))) {
                    $this->snsClient->deleteTopic([
                        'TopicArn' => $topicArn['TopicArn']
                    ]);
                }
            }
            $nextToken = $result->get('NextToken');
        } while ($nextToken);
    }
}