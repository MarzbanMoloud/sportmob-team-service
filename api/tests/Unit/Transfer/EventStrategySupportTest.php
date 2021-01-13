<?php


namespace Tests\Unit\Transfer;


use App\Services\EventStrategy\PlayerWasTransferred;
use App\ValueObjects\Broker\Mediator\Message;
use Symfony\Component\Serializer\SerializerInterface;
use TestCase;


/**
 * Class EventStrategySupportTest
 * @package Tests\Unit\Transfer
 */
class EventStrategySupportTest extends TestCase
{
	private SerializerInterface $serializer;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->serializer = app('Serializer');
	}

	public function testPlayerWasTransferred()
	{
		$message = sprintf('{
            "headers":{
                "event": "%s",
                "priority": "1",
                "date": "2020-11-29T10:49:56+04:30"
            },
            "body":{
                "identifiers": {},
                "metadata": {}
             }
         }', config('mediator-event.events.player_was_transferred'));
		$valueObject = $this->serializer->deserialize($message, Message::class, 'json');
		$this->assertTrue(app(PlayerWasTransferred::class)->support($valueObject));
	}

	public function testPlayerWasTransferredWhenEventIsEmpty()
	{
		$message = sprintf('{
            "headers":{
                "event": "",
                "priority": "1",
                "date": "2020-11-29T10:49:56+04:30"
            },
            "body":{
                "identifiers": {},
                "metadata": {}
             }
         }');
		$valueObject = $this->serializer->deserialize($message, Message::class, 'json');
		$this->assertFalse(app(PlayerWasTransferred::class)->support($valueObject));
	}
}