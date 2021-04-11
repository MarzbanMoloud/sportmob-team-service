<?php


namespace Tests\Unit;


use TestCase;
use Tests\Traits\AmazonBrokerTrait;

class AWSBrokerServiceTest extends TestCase
{
    use AmazonBrokerTrait;

    protected function setUp(): void
    {
        $this->createApplication();
    }

//    protected function tearDown(): void
//    {
//        sleep(60); //Because of you must wait 60 seconds after deleting a queue before you can create another with the same name.
//
//        $this->removeBrokerByTopicNameByQueueName(config('broker.topics.AWSBrokerServiceTest'), config('broker.queues.AWSBrokerServiceTest'));
//    }

    public function testProduceMessage()
    {
        config(['broker.topics.AWSBrokerServiceTest' => env('APP_NAME', 'Lumen') . 'AWSBrokerServiceTest']);
        config(['broker.queues.AWSBrokerServiceTest' => env('APP_NAME', 'Lumen') . 'AWSBrokerServiceTest']);
        $this->setupAWSBroker();

        $FirstMessage = [
            'FirstName' => 'John',
            'Date' => microtime()
        ];
        $SecondMessage = [
            'LastName' => 'Smith',
            'Date' => microtime()
        ];
        $this->brokerService
            ->addMessage('TestFromLocalMessage', json_encode($FirstMessage))
            ->addMessage('TestFromLocalMessage', json_encode($SecondMessage))
            ->produceMessage(config('broker.topics.AWSBrokerServiceTest'));
        $Messages = $this->brokerService->consumePureMessage([config('broker.queues.AWSBrokerServiceTest')], 1);
        $this->assertCount(2, $Messages);
        $this->assertEquals($FirstMessage, json_decode($Messages[0], true));
        $this->assertEquals($SecondMessage, json_decode($Messages[1], true));
    }

}