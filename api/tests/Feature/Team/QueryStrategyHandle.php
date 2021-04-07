<?php


namespace Tests\Feature\Team;


use App\Models\Repositories\TeamRepository;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Support\Facades\Artisan;
use TestCase;
use Tests\Traits\AmazonBrokerTrait;
use Tests\Traits\TeamRepositoryTestTrait;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class QueryStrategyHandle
 * @package Tests\Feature\Team
 */
class QueryStrategyHandle extends TestCase
{
	use TeamRepositoryTestTrait,
		AmazonBrokerTrait;

	private \Faker\Generator $faker;
	private SerializerInterface $serializer;
	private TeamRepository $teamRepository;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->teamRepository = app(TeamRepository::class);
		$this->faker = Factory::create();
		$this->serializer = app(SerializerInterface::class);
		$this->createTeamTable();
		$this->setupAWSBroker();
	}

	public function testHandle()
	{
		$fakeTeamModel = $this->createTeamModel();
		$this->teamRepository->persist($fakeTeamModel);

		$message = (new Message())
			->setHeaders(
				(new Headers())
					->setKey('GetTeamInformation')
					->setId($fakeTeamModel->getId())
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.player_name'))
					->setDate(Carbon::now()->format('c'))
			)->setBody([
				'entity' => config('broker.services.team_name'),
				'id' => $fakeTeamModel->getId(),
			]);
		$this->brokerService->addMessage(
			'GetTeamInformation',
			$this->serializer->serialize($message, 'json')
		)->produceMessage(config('broker.topics.question_team'));

		Artisan::call('broker:consume:query 10 10');

		$response = $this->brokerService->consumePureMessage([config('broker.queues.answer')], 10);
		$response = json_decode(json_encode($response[0]), true);
		$payload = json_decode($response, true);
		$this->assertEquals(config('broker.services.team_name'), $payload['headers']['source']);
		$this->assertEquals(config('broker.services.player_name'), $payload['headers']['destination']);
		$this->assertEquals('GetTeamInformation', $payload['headers']['key']);
		$this->assertEquals($fakeTeamModel->getId(), $payload['headers']['id']);
		$this->assertNotNull($payload['headers']['date']);
		$this->assertEquals($fakeTeamModel->getId(), $payload['body']['id']);
		$this->assertEquals($fakeTeamModel->getCountry(), $payload['body']['country']);
		$this->assertEquals($fakeTeamModel->getCity(), $payload['body']['city']);
		$this->assertEquals($fakeTeamModel->getFounded(), $payload['body']['founded']);
		$this->assertEquals($fakeTeamModel->getGender(), $payload['body']['gender']);
		$this->assertEquals($fakeTeamModel->getName()->getOriginal(), $payload['body']['name']['original']);
		$this->assertEquals($fakeTeamModel->getName()->getOfficial(), $payload['body']['name']['official']);
		$this->assertEquals($fakeTeamModel->getName()->getShort(), $payload['body']['name']['short']);
		$this->assertEquals($fakeTeamModel->getActive(), $payload['body']['active']);
		$this->assertEquals(config('broker.services.team_name'), $payload['body']['entity']);
	}

	public function testHandleWhenItemNotExist()
	{
		$fakeTeamId = $this->faker->uuid;
		$message = (new Message())
			->setHeaders(
				(new Headers())
					->setKey('GetTeamInformation')
					->setId($fakeTeamId)
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.player_name'))
					->setDate(Carbon::now()->format('c'))
			)->setBody([
				'entity' => config('broker.services.team_name'),
				'id' => $fakeTeamId,
			]);
		$this->brokerService->addMessage(
			'GetTeamInformation',
			$this->serializer->serialize($message, 'json')
		)->produceMessage(config('broker.topics.question_team'));

		Artisan::call('broker:consume:query 10 10');

		$response = $this->brokerService->consumePureMessage([config('broker.queues.answer')], 10);
		$response = json_decode(json_encode($response[0]), true);
		$payload = json_decode($response, true);
		$this->assertEquals(config('broker.services.team_name'), $payload['headers']['source']);
		$this->assertEquals(config('broker.services.player_name'), $payload['headers']['destination']);
		$this->assertEquals('GetTeamInformation', $payload['headers']['key']);
		$this->assertEquals($fakeTeamId, $payload['headers']['id']);
		$this->assertNotNull($payload['headers']['date']);
		$this->assertEmpty($payload['body']);
	}

	protected function tearDown(): void
	{
		$this->teamRepository->drop();
	}
}