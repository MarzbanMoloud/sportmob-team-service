<?php


namespace Tests\Unit;


use App\Services\AWS\BrokerService;
use TestCase;
use Tests\Traits\AmazonBrokerTrait;

class AWSBrokerServiceTest extends TestCase
{
    use AmazonBrokerTrait;
    /**
     * @var BrokerService
     */
    private BrokerService $brokerService;

    protected function setUp(): void
    {
        $this->createApplication();
        $this->brokerService = app(BrokerService::class);
        $this->addClient();
    }

    public function testProduceMessage()
    {

        list($topic, $queue) = array_values($this->subscribe('myQueue','myTopic'));
		$this->assertContains($queue,$this->listQueues());
		$this->assertContains($topic,array_map(function($item){return $item['TopicArn'];},$this->listTopics()));
		$this->assertContains($queue, array_map(function($item){return $item['Endpoint'];},$this->listSubscriptions()));
		$this->assertContains($topic, array_map(function($item){return $item['TopicArn'];},$this->listSubscriptions()));

        $msg = [
            "header" => ["event" => "event"], "body" => ['title' => 'title']
        ];

        $result = $this->brokerService->flushMessages()->addMessage('event',json_encode($msg))->produceMessage($topic);
        $this->assertTrue($result);
    }

    public function testConsumeMessage()
    {
        list($topic, $queue) = array_values($this->subscribe('myQueue','myTopic'));
        $msg = [
            "header" => ["event" => "event"], "body" => ['title' => 'title']
        ];

        $result = $this->brokerService->flushMessages()->addMessage('event',json_encode($msg))->produceMessage($topic);
        $this->assertTrue($result);

        $messages = $this->brokerService->consumePureMessage([$queue],20);
        foreach ($messages as $message){
            $this->assertEquals($message,json_encode($msg));
        }
    }

    public function testConsumeMessageException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->brokerService->consumePureMessage([''],20);
    }

    protected function tearDown(): void
    {
		try {
			$this->flushSNS_SQS();
		} catch (\Exception $e) {
		}
    }
}