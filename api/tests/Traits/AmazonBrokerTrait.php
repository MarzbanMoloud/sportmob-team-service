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
        $this->brokerService = app( BrokerService::class );
        $this->snsClient     = $this->brokerService->getSnsClient();
        $this->sqsClient     = $this->brokerService->getSqsClient();
        $this->initAWSBroker();
    }

    private static function getQueuePolicy($queueName, $topicArn)
    {
        $QueueArn = sprintf( "arn:aws:sqs:%s:%s:%s",
                             config( 'aws.sqs.region' ),
                             config( 'aws.account' ),
                             $queueName );
        return [
            'Version'   => '2012-10-17',
            'Id'        => sprintf( "%s/SQSDefaultPolicy", $QueueArn ),
            'Statement' => [
                'Effect'    => 'Allow',
                'Principal' => [ 'Service' => 'sns.amazonaws.com' ],
                'Action'    => [
                    'sqs:SendMessage',
                    'sqs:ReceiveMessage'
                ],
                'Resource'  => $QueueArn
            ]
        ];
    }

    private function initAWSBroker()
    {

        $ExistTopics = [];
        $ExistQueues = [];
        foreach (array_map( function($topic) {
            return $topic[ 'TopicArn' ];
        },
            $this->snsClient->listTopics()->get( 'Topics' ) ) as $topicArn) {
            foreach (config( 'broker.topics' ) as $key => $topic) {
                if (strstr( $topicArn, $topic )) {
                    $ExistTopics[ $key ] = $topicArn;
                }
            }
        }
        foreach ($this->sqsClient->listQueues()->get( 'QueueUrls' ) as $queueUrl) {
            foreach (config( 'broker.queues' ) as $key => $queue) {
                if (strstr( $queueUrl, $queue )) {
                    $ExistQueues[ $key ] = $queueUrl;
                }
            }
        }
        foreach ($ExistTopics as $key => $topic) {
            config( [ sprintf( "broker.topics.%s", $key ) => $topic ] );
        }
        foreach ($ExistQueues as $key => $queueUrl) {
            config( [ sprintf( "broker.queues.%s", $key ) => $queueUrl ] );
        }

        if (!$ExistTopics) {
            foreach (config( 'broker.topics' ) as $key => $topic) {
                $Topic =
                    $this->snsClient->createTopic( [
                                                       'Name'       => sprintf( "%s.fifo", $topic ),
                                                       'Attributes' => [
                                                           'FifoTopic'                 => 'true',
                                                           'ContentBasedDeduplication' => 'false',
                                                       ]
                                                   ] );
                config( [ sprintf( "broker.topics.%s", $key ) => $Topic->get( 'TopicArn' ) ] );
            }
        }
        if (!$ExistQueues) {

            foreach (config( 'broker.queues' ) as $key => $queue) {
                $Queue = $this->sqsClient->createQueue( [
                                                            'QueueName'  => sprintf( "%s.fifo", $queue ),
                                                            'Attributes' => [
                                                                'FifoQueue'                 => 'true',
                                                                'ContentBasedDeduplication' => 'false',
                                                                'Policy'                    => json_encode( self::getQueuePolicy( sprintf( "%s.fifo",
                                                                                                                                           $queue ),
                                                                                                                                  config( 'broker.topics' )[ $key ] ) )
                                                            ]
                                                        ] );
                $QueueAttr[ $key ] =
                    $this->sqsClient->getQueueAttributes( [
                                                              'QueueUrl'       => $Queue->get( 'QueueUrl' ),
                                                              'AttributeNames' => [ 'QueueArn' ]
                                                          ] );

                config( [ sprintf( "broker.queues.%s", $key ) => $Queue->get( 'QueueUrl' ) ] );
                $this->snsClient->subscribe( [
                                                 'Attributes' => [ 'RawMessageDelivery' => 'true' ],
                                                 'Endpoint'   => $QueueAttr[ $key ]->get( 'Attributes' )[ 'QueueArn' ],
                                                 'Protocol'   => 'sqs',
                                                 'TopicArn'   => config( 'broker.topics' )[ $key ]
                                             ] );
            }
        }
    }

    private function tearDownAwsBroker()
    {
        foreach (config( 'broker.queues' ) as $queue) {
            $this->sqsClient->purgeQueue( [ 'QueueUrl' => $queue ] );
        }
    }
}
