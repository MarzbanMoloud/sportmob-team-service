<?php


namespace Tests\Unit\Team;


use App\Services\EventStrategy\TeamWasCreated;
use App\ValueObjects\Broker\Mediator\Message;
use TestCase;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class EventStrategySupportTest
 * @package Tests\Unit\Team
 */
class EventStrategySupportTest extends TestCase
{
	private SerializerInterface $serializer;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->serializer = app('Serializer');
	}

	public function testTeamWasCreated()
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
         }', config('mediator-event.events.team_was_created'));
		$valueObject = $this->serializer->deserialize($message, Message::class, 'json');
		$this->assertTrue(app(TeamWasCreated::class)->support($valueObject));
	}

	public function testTeamWasCreatedWhenEventIsEmpty()
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
		$this->assertFalse(app(TeamWasCreated::class)->support($valueObject));
	}
}