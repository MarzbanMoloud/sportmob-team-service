<?php


namespace Tests\Feature\Team;


use App\Models\ReadModels\Team;
use App\Models\Repositories\TeamRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\ValueObjects\Broker\Mediator\Message as MediatorMessage;
use App\ValueObjects\Broker\Mediator\MessageBody;
use App\ValueObjects\Broker\Mediator\MessageHeader;
use TestCase;
use Faker\Factory;
use Tests\Traits\AmazonBrokerTrait;
use Tests\Traits\TeamRepositoryTestTrait;
use Symfony\Component\Serializer\SerializerInterface;
use Illuminate\Support\Facades\Artisan;


/**
 * Class EventStrategyHandleWithBrokerTest
 * @package Tests\Feature\Team
 */
class EventStrategyHandleWithBrokerTest extends TestCase
{
	use TeamRepositoryTestTrait,
		AmazonBrokerTrait;

	private TeamRepository $teamRepository;
	private \Faker\Generator $faker;
	private TeamCacheServiceInterface $teamCacheService;
	private SerializerInterface $serializer;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->serializer = app('Serializer');
		$this->teamRepository = app(TeamRepository::class);
		$this->teamCacheService = app(TeamCacheServiceInterface::class);
		$this->createTeamTable();
		$this->setupAWSBroker();
	}

	public function testTeamWasCreatedHandle()
	{
		$messageHeader = (new MessageHeader(
			config('mediator-event.events.team_was_created'),
			"1",
			new \DateTimeImmutable()
		));
		$messageBody = (new MessageBody(
			[
				"team" => $this->faker->uuid
			],
			[
				"fullName" => $this->faker->name,
				"shortName" => $this->faker->name,
				"officialName" => $this->faker->name,
				"type" => 'club',
				"country" => $this->faker->country,
				"countryId" => $this->faker->uuid,
				"city" => $this->faker->city,
				"active" => false,
				"founded" => '1234',
				"gender" => 'male'
			]
		));
		$message = (new MediatorMessage())
			->setHeaders($messageHeader)
			->setBody($messageBody);
		$result = $this->brokerService->addMessage(
			config('mediator-event.events.team_was_created'),
			$this->serializer->serialize($message, 'json')
		)->produceMessage(config('broker.topics.event'));
		$this->assertEquals(true, $result);

		Artisan::call('broker:consume:mediator 10 10');

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
		$this->assertEquals($message->getBody()->getMetadata()['countryId'], $response->getCountryId());
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
		$this->assertEquals($message->getBody()->getMetadata()['countryId'], $response->getCountryId());
		$this->assertEquals($message->getBody()->getMetadata()['city'], $response->getCity());
		$this->assertEquals($message->getBody()->getMetadata()['founded'], $response->getFounded());
		$this->assertEquals($message->getBody()->getMetadata()['gender'], $response->getGender());
	}

	protected function tearDown(): void
	{
		$this->teamRepository->drop();
		$this->teamCacheService->flush();
	}
}