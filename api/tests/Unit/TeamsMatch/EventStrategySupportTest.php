<?php


namespace Tests\Unit\TeamsMatch;


use App\Services\EventStrategy\MatchFinished;
use App\Services\EventStrategy\MatchStatusChanged;
use App\Services\EventStrategy\MatchWasCreated;
use App\ValueObjects\Broker\Mediator\Message;
use Symfony\Component\Serializer\SerializerInterface;
use TestCase;


/**
 * Class EventStrategySupportTest
 * @package Tests\Unit\TeamsMatch
 */
class EventStrategySupportTest extends TestCase
{
	private SerializerInterface $serializer;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->serializer = app(SerializerInterface::class);
	}

	public function testMatchWasCreated()
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
         }', config('mediator-event.events.match_was_created'));
		$valueObject = $this->serializer->deserialize($message, Message::class, 'json');
		$this->assertTrue(app(MatchWasCreated::class)->support($valueObject));
	}

	public function testMatchWasCreatedWhenEventIsEmpty()
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
		$this->assertFalse(app(MatchWasCreated::class)->support($valueObject));
	}

	public function testMatchFinished()
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
         }', config('mediator-event.events.match_finished'));
		$valueObject = $this->serializer->deserialize($message, Message::class, 'json');
		$this->assertTrue(app(MatchFinished::class)->support($valueObject));
	}

	public function testMatchFinishedWhenEventIsEmpty()
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
		$this->assertFalse(app(MatchFinished::class)->support($valueObject));
	}

	public function testMatchStatusChanged()
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
         }', config('mediator-event.events.match_status_changed'));
		$valueObject = $this->serializer->deserialize($message, Message::class, 'json');
		$this->assertTrue(app(MatchStatusChanged::class)->support($valueObject));
	}

	public function testMatchStatusChangedWhenEventIsEmpty()
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
		$this->assertFalse(app(MatchStatusChanged::class)->support($valueObject));
	}
}