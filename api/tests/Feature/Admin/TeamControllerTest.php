<?php


namespace Tests\Feature\Admin;


use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Models\ReadModels\Team;
use App\Models\Repositories\TeamRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use Illuminate\Support\Facades\Artisan;
use TestCase;
use Tests\Traits\AmazonBrokerTrait;
use Tests\Traits\TeamRepositoryTestTrait;
use Faker\Factory;
use Illuminate\Http\Response;


/**
 * Class TeamControllerTest
 * @package Tests\Feature\Admin
 */
class TeamControllerTest extends TestCase
{
	use TeamRepositoryTestTrait,
		AmazonBrokerTrait;

	private \Faker\Generator $faker;
	private TeamRepository $teamRepository;
	private TeamCacheServiceInterface $teamCacheService;


	protected function setUp(): void
	{
		parent::setUp();
		$this->createApplication();
		$this->faker = Factory::create();
		$this->teamRepository = app(TeamRepository::class);
		$this->teamCacheService = app(TeamCacheServiceInterface::class);
		$this->createTeamTable();
		$this->setupAWSBroker();
	}

	public function testShow()
	{
		$fakeTeamId = $this->faker->uuid;
		$fakeTeamModel = $this->createTeamModel();
		$fakeTeamModel->setId($fakeTeamId);
		$this->teamRepository->persist($fakeTeamModel);
		$response = $this->json('GET', '/tm/admin/teams/' . $fakeTeamId);
		$response->assertResponseStatus(Response::HTTP_OK);
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertCount(8, $response['data']);
		$this->assertEquals($fakeTeamModel->getId(), $response['data']['id']);
		$this->assertNotEmpty($response['data']['name']);
		$this->assertEquals($fakeTeamModel->getName()->getOriginal(), $response['data']['name']['original']);
		$this->assertEquals($fakeTeamModel->getName()->getOfficial(), $response['data']['name']['official']);
		$this->assertEquals($fakeTeamModel->getName()->getShort(), $response['data']['name']['short']);
		$this->assertEquals($fakeTeamModel->getCountry(), $response['data']['country']);
		$this->assertEquals($fakeTeamModel->getCity(), $response['data']['city']);
		$this->assertEquals($fakeTeamModel->getFounded(), $response['data']['founded']);
		$this->assertEquals($fakeTeamModel->getGender(), $response['data']['gender']);
		$this->assertEquals($fakeTeamModel->isActive(), $response['data']['active']);
		$this->assertEquals($fakeTeamModel->getType(), $response['data']['type']);
	}

	public function testShowWhenItemNotExist()
	{
		$response = $this->json('GET', '/tm/admin/teams/' . $this->faker->uuid);
		$response->assertResponseStatus(Response::HTTP_NOT_FOUND);
		$response = json_decode($response->response->getContent(), true);
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $response['code']);
	}

	public function testUpdate()
	{
		$fakeTeamId = $this->faker->uuid;
		$fakeTeamModel = $this->createTeamModel();
		$fakeTeamModel->setId($fakeTeamId);
		$this->teamRepository->persist($fakeTeamModel);
		$response = $this->put('/tm/admin/teams/' . $fakeTeamId, [
			'name' => [
				'original' => 'Barcelona original',
				'official' => 'Barcelona official',
				'short' => 'Barcelona short',
			]
		]);
		$response->assertResponseStatus(ResponseServiceInterface::STATUS_CODE_UPDATE);
		/**
		 * Handle TeamWasUpdated event.
		 */
		Artisan::call('broker:consume:mediator 10 10');
		/**
		 * Check DB was updated.
		 * @var Team $response
		 */
		$response = $this->teamRepository->find(['id' => $fakeTeamId]);
		$this->assertEquals('Barcelona original', $response->getName()->getOriginal());
		$this->assertEquals('Barcelona official', $response->getName()->getOfficial());
		$this->assertEquals('Barcelona short', $response->getName()->getShort());
		/**
		 * check cache was removed.
		 */
		$response = $this->teamCacheService->getTeam($fakeTeamId);
		$this->assertEquals('Barcelona original', $response->getName()->getOriginal());
		$this->assertEquals('Barcelona official', $response->getName()->getOfficial());
		$this->assertEquals('Barcelona short', $response->getName()->getShort());
	}

	public function testUpdateWhenItemNotExist()
	{
		$response = $this->put('/tm/admin/teams/' . $this->faker->uuid, [
			'name' => [
				'original' => 'Barcelona original',
				'official' => 'Barcelona official',
				'short' => 'Barcelona short',
			]
		]);
		$response->assertResponseStatus(Response::HTTP_NOT_FOUND);
		$response = json_decode($response->response->getContent(), true);
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $response['code']);
	}

	public function testUpdateWithInValidFields()
	{
		$wrongValues = [
			'',
			12345,
			'bar-celona',
			'bar _celona',
			'bar_celona',
			'bar/celona',
			'\bar celona',
			'bar\celona',
			'bar\celona',
			'*bar-celona',
			'bar*celona',
			'bar*celona',
			'bar+celona',
			'bar(celona)',
			'bar[celona]',
			'bar{celona}',
			'bar$',
			'bar?',
		];
		foreach ($wrongValues as $value) {
			$response = $this->put('/tm/admin/teams/' . $this->faker->uuid, [
				'name' => [
					'original' => $value,
				]
			]);
			$response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
			$response = json_decode($response->response->getContent(), true);
			$this->assertNotNull($response['message']);
			$this->assertEquals(config('common.error_codes.validation_failed'), $response['code']);
		}
	}

	protected function tearDown(): void
	{
		$this->teamRepository->drop();
		$this->teamCacheService->flush();
	}
}