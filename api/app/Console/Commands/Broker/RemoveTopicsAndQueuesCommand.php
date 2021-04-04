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

        $this->alert('Topics deleted successfully.');

        $queues = $this->sqsClient->listQueues()->get('QueueUrls') ?: [];
        foreach ($queues as $queueUrl) {
            foreach (config('broker.queues') as $key => $queue) {
                if ($queueUrl == $queue) {
                    $this->sqsClient->purgeQueue(['QueueUrl' => $queue]);
                    $this->sqsClient->deleteQueue(['QueueUrl' => $queue]);
                }
            }
        }

        $this->alert('Queues deleted successfully.');

        $subscriptions = $this->snsClient->listSubscriptions()->get('Subscriptions') ?: [];
        foreach ($subscriptions as $subscription) {
            if (in_array($subscription['TopicArn'], config('broker.topics'))) {
                $this->snsClient->unsubscribe([
                    "SubscriptionArn" => $subscription['SubscriptionArn']
                ]);
            }
        }

        $this->alert('Subscriptions were successfully unsubscribed.');
    }
}