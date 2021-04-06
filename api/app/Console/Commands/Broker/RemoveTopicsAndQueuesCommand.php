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
        $queues = $this->sqsClient->listQueues()->get('QueueUrls') ?: [];
        foreach ($queues as $queueUrl) {
            foreach (config('broker.queues') as $key => $queue) {
                if ($queueUrl == $queue) {
                    $this->sqsClient->purgeQueue(['QueueUrl' => $queue]);
                    $this->sqsClient->deleteQueue(['QueueUrl' => $queue]);
                }
            }
        }
    }

    private function deleteTopics(): void
    {
        foreach (array_map(function ($topic) {
            return $topic['TopicArn'];
        },
            $this->snsClient->listTopics()->get('Topics')) as $topicArn) {
            foreach (config('broker.topics') as $key => $topic) {
                if ($topicArn == $topic) {
                    $this->snsClient->deleteTopic([
                        'TopicArn' => $topicArn
                    ]);
                }
            }
        }
    }
}