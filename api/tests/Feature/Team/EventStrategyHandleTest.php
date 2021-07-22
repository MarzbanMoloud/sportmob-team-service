<?php


namespace Tests\Feature\Team;


use App\Exceptions\Projection\ProjectionException;
use App\Http\Services\Team\TeamService;
use App\Http\Services\TeamsMatch\TeamsMatchService;
use App\Http\Services\Transfer\TransferService;
use App\Http\Services\Trophy\TrophyService;
use App\Models\ReadModels\Team;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TeamsMatchRepository;
use App\Models\Repositories\TransferRepository;
use App\Models\Repositories\TrophyRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\Services\EventStrategy\TeamWasCreated;
use App\Services\EventStrategy\TeamWasUpdated;
use Carbon\Carbon;
use Symfony\Component\Serializer\SerializerInterface;
use TestCase;
use Faker\Factory;
use App\ValueObjects\Broker\Mediator\Message;
use Tests\Traits\TeamRepositoryTestTrait;
use Tests\Traits\TeamsMatchRepositoryTestTrait;
use Tests\Traits\TransferRepositoryTestTrait;
use Tests\Traits\TrophyRepositoryTestTrait;


/**
 * Class EventStrategyHandleTest
 * @package Tests\Feature\Team
 */
class EventStrategyHandleTest extends TestCase
{
	use TeamRepositoryTestTrait,
		TransferRepositoryTestTrait,
		TrophyRepositoryTestTrait,
		TeamsMatchRepositoryTestTrait;

	private TeamRepository $teamRepository;
	private \Faker\Generator $faker;
	private TeamCacheServiceInterface $teamCacheService;
	private SerializerInterface $serializer;
	private TeamsMatchRepository $teamsMatchRepository;
	private TrophyRepository $trophyRepository;
	private TransferRepository $transferRepository;
	private TransferService $transferService;
	private TrophyService $trophyService;
	private TeamsMatchService $teamsMatchService;
	private TeamService $teamService;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->teamRepository = app(TeamRepository::class);
		$this->teamService = app(TeamService::class);
		$this->teamsMatchRepository = app(TeamsMatchRepository::class);
		$this->teamsMatchService = app(TeamsMatchService::class);
		$this->transferRepository = app(TransferRepository::class);
		$this->transferService = app(TransferService::class);
		$this->trophyRepository = app(TrophyRepository::class);
		$this->trophyService = app(TrophyService::class);
		$this->teamCacheService = app(TeamCacheServiceInterface::class);
		$this->serializer = app(SerializerInterface::class);
		$this->createTeamTable();
		$this->createTransferTable();
		$this->createTeamsMatchTable();
		$this->createTrophyTable();
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
					"countryId": "10458356-653d-11eb-ae93-0242ac130002",
					"city": "Manchester",
					"active": true,
					"founded": "1234",
					"gender": "male"
				}
			}
		}',
			config('mediator-event.events.team_was_created'),
			Carbon::now()->format('c'),
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(TeamWasCreated::class)->handle($message);
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
		$this->assertEquals($message->getBody()->getMetadata()['country'], $response->getCountry());
		$this->assertEquals($message->getBody()->getMetadata()['countryId'], $response->getCountryId());
		$this->assertEquals($message->getBody()->getMetadata()['city'], $response->getCity());
		$this->assertEquals($message->getBody()->getMetadata()['founded'], $response->getFounded());
		$this->assertEquals($message->getBody()->getMetadata()['gender'], $response->getGender());
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
			Carbon::now()->format('c'));
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(TeamWasCreated::class)->handle($message);
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
			Carbon::now()->format('c'),
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(TeamWasCreated::class)->handle($message);
	}

	public function testTeamWasUpdatedHandle()
	{
		/** Team */
		$teamModel = $this->createTeamModel();
		$this->teamRepository->persist($teamModel);

		$this->teamService->findTeamById($teamModel->getId());
		/** Transfer */
		//TODO:: check it later
	/*	$transferModel = $this->createTransferModel();
		$transferModel
			->setToTeamId($teamModel->getId())
			->setToTeamName($teamModel->getName()->getOriginal());
		$transferModel->prePersist();
		$this->transferRepository->persist($transferModel);

		$this->transferService->listByTeam($transferModel->getToTeamId(), $transferModel->getSeason());
		$this->transferService->listByPlayer($transferModel->getPlayerId());*/
		/** Trophy */
		$trophyModel = $this->createTrophyModel();
		$trophyModel
			->setTeamId($teamModel->getId())
			->setTeamName($teamModel->getName()->getOfficial());
		$this->trophyRepository->persist($trophyModel);

		$this->trophyService->getTrophiesByTeam($trophyModel->getTeamId());
		$this->trophyService->getTrophiesByCompetition($trophyModel->getCompetitionId());
		/** TeamsMatch */
		$teamsMatchModel = $this->createTeamsMatchModel(
			$teamModel->getId(),
			$this->faker->uuid,
			$teamModel->getName()->getOriginal(),
			$this->faker->name,
			$this->faker->uuid,
		);
		$this->teamsMatchRepository->persist($teamsMatchModel);

		$this->teamsMatchService->getTeamsMatchInfo($teamsMatchModel->getTeamId());

		/** @var $message */
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
					"fullName": "%s",
					"shortName": "%s",
					"officialName": ""
				}
			}
		}',
			config('mediator-event.events.team_was_updated'),
			Carbon::now()->format('c'),
			$teamModel->getId(),
			$this->faker->name,
			$this->faker->name,
		);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(TeamWasUpdated::class)->handle($message);
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
		$this->assertEquals($teamModel->getType(), $response->getType());
		$this->assertEquals($teamModel->getCountry(), $response->getCountry());
		$this->assertEquals($teamModel->getCountryId(), $response->getCountryId());
		$this->assertEquals($teamModel->getCity(), $response->getCity());
		$this->assertEquals($teamModel->getFounded(), $response->getFounded());
		$this->assertEquals($teamModel->getGender(), $response->getGender());
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
		$this->assertEquals($teamModel->getType(), $response->getType());
		$this->assertEquals($teamModel->getCountry(), $response->getCountry());
		$this->assertEquals($teamModel->getCountryId(), $response->getCountryId());
		$this->assertEquals($teamModel->getCity(), $response->getCity());
		$this->assertEquals($teamModel->getFounded(), $response->getFounded());
		$this->assertEquals($teamModel->getGender(), $response->getGender());
		/**
		 * Transfer
		 * Read from DB.
		 */
		/*$response = $this->transferRepository->findByTeamId($transferModel->getToTeamId());
		$this->assertNotEmpty($response);
		$this->assertEquals($message->getBody()->getMetadata()['fullName'], $response[0]->getToTeamName());*/
		/**
		 * Transfer
		 * Read from Cache.
		 */
		/*$response = $this->transferService->listByTeam($transferModel->getToTeamId(), $transferModel->getSeason());
		$this->assertNotEmpty($response);
		$this->assertEquals($message->getBody()->getMetadata()['fullName'], $response[0]->getToTeamName());
		$response = $this->transferService->listByPlayer($transferModel->getPlayerId());
		$this->assertNotEmpty($response);
		$this->assertEquals($message->getBody()->getMetadata()['fullName'], $response[0]->getToTeamName());*/
		/**
		 * Trophy
		 * Read from DB.
		 */
		$response = $this->trophyRepository->findByCompetition($trophyModel->getCompetitionId());
		$this->assertNotEmpty($response);
		$this->assertEquals($message->getBody()->getMetadata()['officialName'], $response[0]->getTeamName());
		/**
		 * Trophy
		 * Read from Cache.
		 */
		$response = $this->trophyService->getTrophiesByCompetition($trophyModel->getCompetitionId());
		$this->assertNotEmpty($response);
		$this->assertEquals($message->getBody()->getMetadata()['officialName'], $response[0]->getTeamName());
		$response = $this->trophyService->getTrophiesByTeam($trophyModel->getTeamId());
		$this->assertNotEmpty($response);
		$this->assertEquals($message->getBody()->getMetadata()['officialName'], $response[0]->getTeamName());
		/**
		 * TeamsMatch
		 * Read from DB
		 */
		$response = $this->teamsMatchRepository->findTeamsMatchByTeamId($teamsMatchModel->getTeamId(), TeamsMatch::STATUS_UPCOMING);
		$this->assertNotEmpty($response);
		$this->assertEquals($message->getBody()->getMetadata()['fullName'], $response[0]->getTeamName()->getOriginal());
		$this->assertEquals($message->getBody()->getMetadata()['shortName'], $response[0]->getTeamName()->getShort());
		$this->assertEquals($message->getBody()->getMetadata()['officialName'], $response[0]->getTeamName()->getOfficial());
		/**
		 * TeamsMatch
		 * Read from Cache
		 */
		$response = $this->teamsMatchService->getTeamsMatchInfo($teamsMatchModel->getTeamId());
		$this->assertNotEmpty($response);
		$this->assertEquals($message->getBody()->getMetadata()['fullName'], $response['team']['name']['original']);
		$this->assertEquals($message->getBody()->getMetadata()['shortName'], $response['team']['name']['short']);
		$this->assertEquals($message->getBody()->getMetadata()['officialName'], $response['team']['name']['official']);
		$this->assertEquals($message->getBody()->getMetadata()['fullName'], $response['upcoming'][0]->getTeamName()->getOriginal());
		$this->assertEquals($message->getBody()->getMetadata()['shortName'], $response['upcoming'][0]->getTeamName()->getShort());
		$this->assertEquals($message->getBody()->getMetadata()['officialName'], $response['upcoming'][0]->getTeamName()->getOfficial());
	}

	public function testTeamWasUpdatedHandleWhenTeamNotExist()
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
					"fullName": "%s",
					"shortName": "%s",
					"officialName": ""
				}
			}
		}',
			config('mediator-event.events.team_was_updated'),
			Carbon::now()->format('c'),
			$this->faker->uuid,
			$this->faker->name,
			$this->faker->name,
		);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(TeamWasUpdated::class)->handle($message);
	}

	public function testTeamWasUpdatedHandleWhenIdentifierIsWrong()
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
			config('mediator-event.events.team_was_updated'),
			Carbon::now()->format('c')
		);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(TeamWasUpdated::class)->handle($message);
	}

	public function testTeamWasUpdatedHandleWhenMetaDataIsWrong()
	{
		$this->expectException(ProjectionException::class);
		$teamModel = $this->createTeamModel();
		$this->teamRepository->persist($teamModel);
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
					"fullName": "",
					"shortName": "",
					"officialName": ""
				}
			}
		}',
			config('mediator-event.events.team_was_updated'),
			Carbon::now()->format('c'),
			$teamModel->getId()
		);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(TeamWasUpdated::class)->handle($message);
	}

	protected function tearDown(): void
	{
		$this->teamRepository->drop();
		$this->teamCacheService->flush();
	}
}