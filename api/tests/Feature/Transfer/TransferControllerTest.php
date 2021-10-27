<?php


namespace Tests\Feature\Transfer;


use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use Symfony\Component\Serializer\SerializerInterface;
use TestCase;
use Faker\Factory;
use Tests\Traits\TeamRepositoryTestTrait;
use Tests\Traits\TransferRepositoryTestTrait;
use Illuminate\Http\Response;


/**
 * Class TransferControllerTest
 * @package Tests\Feature\Transfer
 */
class TransferControllerTest extends TestCase
{
	use TransferRepositoryTestTrait, TeamRepositoryTestTrait;

	private TransferRepository $transferRepository;
	private TransferCacheServiceInterface $transferCacheServiceInterface;
	private \Faker\Generator $faker;
	private SerializerInterface $serializer;
	private TeamRepository $teamRepository;

	protected function setUp(): void
	{
		parent::setUp();
		$this->createApplication();
		$this->transferRepository = app(TransferRepository::class);
		$this->teamRepository = app(TeamRepository::class);
		$this->transferCacheServiceInterface = app(TransferCacheServiceInterface::class);
		$this->serializer = app(SerializerInterface::class);
		$this->faker = Factory::create();
		$this->createTransferTable();
		$this->createTeamTable();
	}

	public function testListByTeamWithSeasonWhenItemNotExist()
	{
		$response = $this->json('GET', sprintf('/en/transfers/team/%s/%s', $this->faker->uuid, '2019-2020'));
		$response->assertResponseStatus(Response::HTTP_NOT_FOUND);
		$response = json_decode($response->response->getContent(), true);;
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $response['code']);
	}

	public function testListByTeamWithOutSeasonWhenItemNotExist()
	{
		$response = $this->json('GET', sprintf('/en/transfers/team/%s', $this->faker->uuid));
		$response->assertResponseStatus(Response::HTTP_NOT_FOUND);
		$response = json_decode($response->response->getContent(), true);
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $response['code']);
	}

	public function testListByTeamWithSeason()
	{
		$fakeTeamId = $this->faker->uuid;
		$this->persistBatchDataForTeam($fakeTeamId);
		/**
		 * Read from DB.
		 */
		$this->transferCacheServiceInterface->flush();
		$response = $this->json('GET', sprintf('/en/transfers/team/%s/%s', $fakeTeamId, '2015-2016'));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertCount(2, $response['data']);
		foreach ($response['data'] as $transferItem) {
			$this->assertNotNull($transferItem['id']);
			$this->assertNotEmpty($transferItem['toTeam']);
			$this->assertNotNull($transferItem['toTeam']['id']);
			$this->assertNotNull($transferItem['toTeam']['name']['full']);
			$this->assertNull($transferItem['fromTeam']);
			$this->assertNotEmpty($transferItem['person']);
			$this->assertNotNull($transferItem['person']['id']);
			$this->assertNotNull($transferItem['person']['name']);
			$this->assertNotNull($transferItem['person']['name']['full']);
			$this->assertNotNull($transferItem['marketValue']);
			$this->assertNotNull($transferItem['startDate']);
			$this->assertNotNull($transferItem['endDate']);
			$this->assertNotNull($transferItem['type']);
			$this->assertNotNull($transferItem['like']);
			$this->assertNotNull($transferItem['dislike']);
			$this->assertNotNull($transferItem['season']);
		}
		/**
		 * Read from Cache.
		 */
		$this->transferRepository->drop();
		$response = $this->json('GET', sprintf('/en/transfers/team/%s/%s', $fakeTeamId, '2015-2016'));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertCount(2, $response['data']);
		foreach ($response['data'] as $transferItem) {
			$this->assertNotNull($transferItem['id']);
			$this->assertNotEmpty($transferItem['toTeam']);
			$this->assertNotNull($transferItem['toTeam']['id']);
			$this->assertNotNull($transferItem['toTeam']['name']['full']);
			$this->assertNull($transferItem['fromTeam']);
			$this->assertNotEmpty($transferItem['person']);
			$this->assertNotNull($transferItem['person']['id']);
			$this->assertNotNull($transferItem['person']['name']);
			$this->assertNotNull($transferItem['person']['name']['full']);
			$this->assertNotNull($transferItem['marketValue']);
			$this->assertNotNull($transferItem['startDate']);
			$this->assertNotNull($transferItem['endDate']);
			$this->assertNotNull($transferItem['type']);
			$this->assertNotNull($transferItem['like']);
			$this->assertNotNull($transferItem['dislike']);
			$this->assertNotNull($transferItem['season']);
		}
	}

	public function testListByTeamWithOutSeason()
	{
		$fakeTeamId = $this->faker->uuid;
		$this->persistBatchDataForTeam($fakeTeamId);
		/**
		 * Read from DB.
		 */
		$this->transferCacheServiceInterface->flush();
		$response = $this->json('GET', sprintf('/en/transfers/team/%s', $fakeTeamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['transfers']);
		$this->assertCount(1, $response['data']['transfers']);
		foreach ($response['data']['transfers'] as $transferItem) {
			$this->assertNotNull($transferItem['id']);
			$this->assertNotEmpty($transferItem['person']);
			$this->assertNotNull($transferItem['person']['id']);
			$this->assertNotNull($transferItem['person']['name']);
			$this->assertNotEmpty($transferItem['team']);
			$this->assertCount(2, $transferItem['team']);
			$this->assertNotEmpty($transferItem['team']['to']);
			$this->assertNotNull($transferItem['team']['to']['id']);
			$this->assertNotNull($transferItem['team']['to']['name']);
			$this->assertNotEmpty($transferItem['team']['from']);
			$this->assertNotNull($transferItem['team']['from']['id']);
			$this->assertNotNull($transferItem['team']['from']['name']);
			$this->assertNotNull($transferItem['marketValue']);
			$this->assertNotNull($transferItem['startDate']);
			$this->assertNotNull($transferItem['endDate']);
			$this->assertNotNull($transferItem['announcedDate']);
			$this->assertNotNull($transferItem['contractDate']);
			$this->assertNotNull($transferItem['type']);
			$this->assertNotNull($transferItem['like']);
			$this->assertNotNull($transferItem['dislike']);
			$this->assertNotNull($transferItem['season']);
		}
		$this->assertNotEmpty($response['data']['seasons']);
		$this->assertCount(8, $response['data']['seasons']);
		/**
		 * Read from Cache.
		 */
		$this->transferRepository->drop();
		$response = $this->json('GET', sprintf('/en/transfers/team/%s', $fakeTeamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['transfers']);
		$this->assertCount(1, $response['data']['transfers']);
		foreach ($response['data']['transfers'] as $transferItem) {
			$this->assertNotNull($transferItem['id']);
			$this->assertNotEmpty($transferItem['person']);
			$this->assertNotNull($transferItem['person']['id']);
			$this->assertNotNull($transferItem['person']['name']);
			$this->assertNotEmpty($transferItem['team']);
			$this->assertCount(2, $transferItem['team']);
			$this->assertNotEmpty($transferItem['team']['to']);
			$this->assertNotNull($transferItem['team']['to']['id']);
			$this->assertNotNull($transferItem['team']['to']['name']);
			$this->assertNotEmpty($transferItem['team']['from']);
			$this->assertNotNull($transferItem['team']['from']['id']);
			$this->assertNotNull($transferItem['team']['from']['name']);
			$this->assertNotNull($transferItem['marketValue']);
			$this->assertNotNull($transferItem['startDate']);
			$this->assertNotNull($transferItem['endDate']);
			$this->assertNotNull($transferItem['announcedDate']);
			$this->assertNotNull($transferItem['contractDate']);
			$this->assertNotNull($transferItem['type']);
			$this->assertNotNull($transferItem['like']);
			$this->assertNotNull($transferItem['dislike']);
			$this->assertNotNull($transferItem['season']);
		}
		$this->assertNotEmpty($response['data']['seasons']);
		$this->assertCount(8, $response['data']['seasons']);
	}

	public function testListByPerson()
	{
		$personId = $this->faker->uuid;
		$this->persistBatchDataForPerson($personId);

		/** Read from DB. */
		$this->transferCacheServiceInterface->flush();

		$response = $this->json('GET', sprintf('/en/transfers/person/%s', $personId));
		$response = json_decode($response->response->getContent(), true);

		foreach ($response['data'] as $key => $transferItem) {
			$this->assertNotNull($transferItem['id']);
			$this->assertNotEmpty($transferItem['person']);
			$this->assertNotNull($transferItem['person']['id']);
			$this->assertNull($transferItem['person']['name']);
			$this->assertNotEmpty($transferItem['team']);
			$this->assertNotEmpty($transferItem['team']['to']);
			$this->assertNotNull($transferItem['team']['to']['id']);
			$this->assertNotNull($transferItem['team']['to']['name']);

			if ($key != 0) {
				$this->assertNotEmpty($transferItem['team']['from']);
				$this->assertNotNull($transferItem['team']['from']['id']);
				$this->assertNotNull($transferItem['team']['from']['name']);
			}

			$this->assertNull($transferItem['marketValue']);
			$this->assertNotNull($transferItem['startDate']);
			$this->assertNull($transferItem['announcedDate']);
			$this->assertNull($transferItem['contractDate']);
			$this->assertNotNull($transferItem['type']);
			$this->assertNotNull($transferItem['like']);
			$this->assertNotNull($transferItem['dislike']);
			$this->assertNotNull($transferItem['season']);
		}
		/**
		 * Read from Cache.
		 */
		$this->transferRepository->drop();
		$response = $this->json('GET', sprintf('/en/transfers/person/%s', $personId));
		$response = json_decode($response->response->getContent(), true);
		foreach ($response['data'] as $key => $transferItem) {
			$this->assertNotNull($transferItem['id']);
			$this->assertNotEmpty($transferItem['person']);
			$this->assertNotNull($transferItem['person']['id']);
			$this->assertNull($transferItem['person']['name']);
			$this->assertNotEmpty($transferItem['team']);
			$this->assertNotEmpty($transferItem['team']['to']);
			$this->assertNotNull($transferItem['team']['to']['id']);
			$this->assertNotNull($transferItem['team']['to']['name']);
			if ($key != 0) {
				$this->assertNotEmpty($transferItem['team']['from']);
				$this->assertNotNull($transferItem['team']['from']['id']);
				$this->assertNotNull($transferItem['team']['from']['name']);
			}
			$this->assertNull($transferItem['marketValue']);
			$this->assertNotNull($transferItem['startDate']);
			$this->assertNull($transferItem['announcedDate']);
			$this->assertNull($transferItem['contractDate']);
			$this->assertNotNull($transferItem['type']);
			$this->assertNotNull($transferItem['like']);
			$this->assertNotNull($transferItem['dislike']);
			$this->assertNotNull($transferItem['season']);
		}
	}

	public function testListByPersonWhenItemNotExist()
	{
		$response = $this->json('GET', sprintf('/en/transfers/person/%s', $this->faker->uuid));
		$response->assertResponseStatus(Response::HTTP_NOT_FOUND);
		$response = json_decode($response->response->getContent(), true);
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $response['code']);
	}

	/**
	 * Example: for team transfer.
	 */
	public function testUserActionTransfer()
	{
		$userId = $this->faker->uuid;
		$fakeTeamId = $this->faker->uuid;
		$this->persistBatchDataForTeam($fakeTeamId);
		$response = $this->json('GET', sprintf('/en/transfers/team/%s/%s', $fakeTeamId, '2019-2020'));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEquals(0, $response['data']['transfers'][0]['like']);
		$this->assertEquals(0, $response['data']['transfers'][0]['dislike']);
		/**
		 * First time.
		 */
		$result = $this->json('PUT', sprintf('/en/transfers/like/%s',
			$response['data']['transfers'][0]['id']), ['userId' => $userId]);

		/**
		 * @var Transfer $transferItem
		 */
		$transferItem = $this->transferRepository->find([
			'id' => $response['data']['transfers'][0]['id']
		]);
		$result->assertResponseStatus(ResponseServiceInterface::STATUS_CODE_UPDATE);
		$this->assertEquals(1, $transferItem->getLike());
		$this->assertEquals(0, $transferItem->getDislike());
		/**
		 * Second time.
		 */
		$result = $this->json('PUT', sprintf('/en/transfers/like/%s',
			$response['data']['transfers'][0]['id']), ['userId' => $userId]);
		/**
		 * @var Transfer $transferItem
		 */
		$transferItem = $this->transferRepository->find([
			'id' => $response['data']['transfers'][0]['id']
		]);
		$result = json_decode($result->response->getContent(), true);
		$this->assertEquals(1, $transferItem->getLike());
		$this->assertEquals(0, $transferItem->getDislike());
		$this->assertNotNull($result['message']);
		$this->assertEquals(config('common.error_codes.User_is_not_allowed_to_like'), $result['code']);
		/**
		 * Third time.
		 */
		$result = $this->json('PUT', sprintf('/en/transfers/dislike/%s',
			$response['data']['transfers'][0]['id']), ['userId' => $userId]);
		/**
		 * @var Transfer $transferItem
		 */
		$transferItem = $this->transferRepository->find([
			'id' => $response['data']['transfers'][0]['id']
		]);
		$result->assertResponseStatus(ResponseServiceInterface::STATUS_CODE_UPDATE);
		$this->assertEquals(0, $transferItem->getLike());
		$this->assertEquals(1, $transferItem->getDislike());
	}

	public function testUserActionTransferWhenItemNotExist()
	{
		$result = $this->json('PUT', sprintf('/en/transfers/like/%s', $this->faker->uuid),
		[
			'userId' => $this->faker->uuid
		]);
		$result = json_decode($result->response->getContent(), true);
		$this->assertNotNull($result['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $result['code']);
	}

	protected function tearDown(): void
	{
		$this->transferCacheServiceInterface->flush();
		$this->transferRepository->drop();
	}
}