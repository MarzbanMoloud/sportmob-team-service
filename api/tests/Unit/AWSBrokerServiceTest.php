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
        $this->setupAWSBroker();
    }

    public function testProduceMessage()
    {
        $FirstMessage  = [
            'FirstName' => 'John',
            'Date'      => microtime()
        ];
        $SecondMessage = [
            'LastName' => 'Smith',
            'Date'     => microtime()
        ];
        $this->brokerService->addMessage( 'TestFromLocalMessage', json_encode( $FirstMessage ) )
                            ->addMessage( 'TestFromLocalMessage', json_encode( $SecondMessage ) )
                            ->produceMessage( config('broker.topics.event') );
        $Messages = $this->brokerService->consumePureMessage( [ config('broker.queues.event') ], 1 );
        $this->assertCount( 2, $Messages );
        $this->assertEquals( $FirstMessage, json_decode( $Messages[ 0 ], true ) );
        $this->assertEquals( $SecondMessage, json_decode( $Messages[ 1 ], true ) );
    }

}