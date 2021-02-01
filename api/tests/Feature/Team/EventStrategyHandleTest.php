<?php


namespace Tests\Feature\Team;


use App\Exceptions\Projection\ProjectionException;
use App\Models\ReadModels\Team;
use App\Models\Repositories\TeamRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\Services\EventStrategy\TeamWasCreated;
use Carbon\Carbon;
use TestCase;
use Faker\Factory;
use App\ValueObjects\Broker\Mediator\Message;
use Tests\Traits\TeamRepositoryTestTrait;


/**
 * Class EventStrategyHandleTest
 * @package Tests\Feature\Team
 */
class EventStrategyHandleTest extends TestCase
{
	use TeamRepositoryTestTrait;

	private TeamRepository $teamRepository;
	private \Faker\Generator $faker;
	private TeamCacheServiceInterface $teamCacheService;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->teamRepository = app(TeamRepository::class);
		$this->teamCacheService = app(TeamCacheServiceInterface::class);
		$this->createTeamTable();
	}

	public function testTeamWasCreatedHandle()
	{
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"team":"%s"
				 },
				"metadata": {
					"fullName": "Barcelona",
					"shortName": "Barcelona_short",
					"officialName": "Barcelona_official",
					"type": "club",
					"country": "England",
					"city": "Manchester",
					"active": true,
					"founded": "1234",
					"gender": "male"
				}
			}
		}',
			config('mediator-event.events.team_was_created'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(TeamWasCreated::class)->handle($message->getBody());
		/**
		 * @var Team $response
		 * Read from DB.
		 */
		$response = $this->teamRepository->find(['id' => $message->getBody()->getIdentifiers()['team']]);
		$this->assertNotEmpty($response);
		$this->assertEquals($message->getBody()->getIdentifiers()['team'], $response->getId());
		$this->assertEquals($message->getBody()->getMetadata()['fullName'], $response->getName()->getOriginal());
		$this->assertEquals($message->getBody()->getMetadata()['shortName'], $response->getName()->getShort());
		$this->assertEquals($message->getBody()->getMetadata()['officialName'], $response->getName()->getOfficial());
		$this->assertEquals($message->getBody()->getMetadata()['type'], $response->getType());
		$this->assertEquals($message->getBody()->getMetadata()['country'], $response->getCountry());
		$this->assertEquals($message->getBody()->getMetadata()['city'], $response->getCity());
		$this->assertEquals($message->getBody()->getMetadata()['founded'], $response->getFounded());
		$this->assertEquals($message->getBody()->getMetadata()['gender'], $response->getGender());
		/**
		 * @var Team $response
		 * Read from Cache.
		 */
		$response = $this->teamCacheService->getTeam($message->getBody()->getIdentifiers()['team']);
		$this->assertNotEmpty($response);
		$this->assertEquals($message->getBody()->getIdentifiers()['team'], $response->getId());
		$this->assertEquals($message->getBody()->getMetadata()['fullName'], $response->getName()->getOriginal());
		$this->assertEquals($message->getBody()->getMetadata()['shortName'], $response->getName()->getShort());
		$this->assertEquals($message->getBody()->getMetadata()['officialName'], $response->getName()->getOfficial());
		$this->assertEquals($message->getBody()->getMetadata()['type'], $response->getType());
		$this->assertEquals($message->getBody()->getMetadata()['country'], $response->getCountry());
		$this->assertEquals($message->getBody()->getMetadata()['city'], $response->getCity());
		$this->assertEquals($message->getBody()->getMetadata()['founded'], $response->getFounded());
		$this->assertEquals($message->getBody()->getMetadata()['gender'], $response->getGender());
	}

	public function testTeamWasCreatedHandleWhenTeamExist()
	{
		$this->expectException(ProjectionException::class);
		$fakeTeamModel = $this->createTeamModel();
		$this->teamRepository->persist($fakeTeamModel);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"team":"%s"
				 },
				"metadata": {
					"fullName": "Barcelona",
					"shortName": "Barcelona_short",
					"officialName": "Barcelona_official",
					"type": "club",
					"country": "England",
					"city": "Manchester",
					"active": true,
					"founded": "1234",
					"gender": "male"
				}
			}
		}',
			config('mediator-event.events.team_was_created'),
			Carbon::now()->toDateTimeString(),
			$fakeTeamModel->getId());
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(TeamWasCreated::class)->handle($message->getBody());
	}

	public function testTeamWasCreatedHandleWhenIdentifierIsNull()
	{
		$this->expectException(ProjectionException::class);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"team":""
				 },
				"metadata": {}
			}
		}',
			config('mediator-event.events.team_was_created'),
			Carbon::now()->toDateTimeString());
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(TeamWasCreated::class)->handle($message->getBody());
	}

	public function testTeamWasCreatedHandleWhenMetadataIsWrong()
	{
		$this->expectException(ProjectionException::class);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"team":"%s"
				 },
				"metadata": {
					"fullName": "Barcelona",
					"shortName": "Barcelona_short",
					"officialName": "Barcelona_official"
				}
			}
		}',
			config('mediator-event.events.team_was_created'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(TeamWasCreated::class)->handle($message->getBody());
	}

	protected function tearDown(): void
	{
		$this->teamRepository->drop();
		$this->teamCacheService->flush();
	}
}