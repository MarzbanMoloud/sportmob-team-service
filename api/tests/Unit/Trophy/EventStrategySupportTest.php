<?php


namespace Tests\Unit\Trophy;


use App\Services\EventStrategy\TeamBecameRunnerUp;
use App\Services\EventStrategy\TeamBecameWinner;
use Symfony\Component\Serializer\SerializerInterface;
use App\ValueObjects\Broker\Mediator\Message;
use TestCase;


/**
 * Class EventStrategySupportTest
 * @package Tests\Unit\Trophy
 */
class EventStrategySupportTest extends TestCase
{
	private SerializerInterface $serializer;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->serializer = app(SerializerInterface::class);
	}

	public function testTeamBecameWinner()
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
         }', config('mediator-event.events.team_became_winner'));
		$valueObject = $this->serializer->deserialize($message, Message::class, 'json');
		$this->assertTrue(app(TeamBecameWinner::class)->support($valueObject));
	}

	public function testTeamBecameWinnerWhenEventIsEmpty()
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
		$this->assertFalse(app(TeamBecameWinner::class)->support($valueObject));
	}

	public function testTeamBecameRunnerUp()
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
         }', config('mediator-event.events.team_became_runner_up'));
		$valueObject = $this->serializer->deserialize($message, Message::class, 'json');
		$this->assertTrue(app(TeamBecameRunnerUp::class)->support($valueObject));
	}

	public function testTeamBecameRunnerUpWhenEventIsEmpty()
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
		$this->assertFalse(app(TeamBecameRunnerUp::class)->support($valueObject));
	}
}