<?php


namespace Tests\Unit\Team;


use App\Models\ReadModels\Team;
use App\Models\Repositories\TeamRepository;
use Illuminate\Http\Response;
use TestCase;
use Faker\Factory;
use Tests\Traits\TeamRepositoryTestTrait;


/**
 * Class TeamRepositoryTest
 * @package Tests\Unit\Team
 */
class TeamRepositoryTest extends TestCase
{
	use TeamRepositoryTestTrait;

	private TeamRepository $teamRepository;
	private \Faker\Generator $faker;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->teamRepository = app(TeamRepository::class);
		$this->createTeamTable();
	}

	public function testGetTableName()
	{
		$response = $this->teamRepository->getTableName();
		$this->assertEquals(TeamRepository::getTableName(), $response);
	}

	public function testCreateTable()
	{
		$this->assertContains(TeamRepository::getTableName(), $this->teamRepository->getDynamoDbClient()->listTables()->toArray()['TableNames']);
	}

	public function testPersist()
	{
		$fakeTeamModel = $this->createTeamModel();
		$result = $this->teamRepository->persist($fakeTeamModel);
		$this->assertEquals( Response::HTTP_OK, $result->toArray()[ '@metadata' ][ 'statusCode' ] );
		$response = $this->teamRepository->findAll();
		$this->assertNotEmpty($response);

		/**
		 * @var Team $response
		 */
		$response = $response[0];
		$this->assertEquals($fakeTeamModel->getId(), $response->getId());
		$this->assertEquals($fakeTeamModel->getName(), $response->getName());
		$this->assertEquals($fakeTeamModel->getCountry(), $response->getCountry());
		$this->assertEquals($fakeTeamModel->getGender(), $response->getGender());
		$this->assertEquals($fakeTeamModel->getCity(), $response->getCity());
		$this->assertEquals($fakeTeamModel->getFounded(), $response->getFounded());
		$this->assertEquals($fakeTeamModel->getType(), $response->getType());
		$this->assertEquals($fakeTeamModel->getActive(), $response->getActive());

	}

	public function testFindById()
	{
		$fakeTeamModel = $this->createTeamModel();
		$this->teamRepository->persist($fakeTeamModel);
		$response = $this->teamRepository->find(['id' => $fakeTeamModel->getId()]);
		$this->assertInstanceOf(Team::class, $response);
	}

	public function testFindByIdWhenItemNotExist()
	{
		$response = $this->teamRepository->find(['id' => $this->faker->uuid]);
		$this->assertEmpty($response);
	}

	protected function tearDown(): void
	{
		$this->teamRepository->drop();
	}
}