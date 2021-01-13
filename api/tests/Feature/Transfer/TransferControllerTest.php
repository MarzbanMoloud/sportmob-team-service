<?php


namespace Tests\Feature\Transfer;


use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\Utilities\Utility;
use TestCase;
use Faker\Factory;
use Tests\Traits\TransferRepositoryTestTrait;
use Illuminate\Http\Response;


/**
 * Class TransferControllerTest
 * @package Tests\Feature\Transfer
 */
class TransferControllerTest extends TestCase
{
	use TransferRepositoryTestTrait;

	private TransferRepository $transferRepository;
	private TransferCacheServiceInterface $transferCacheServiceInterface;
	private \Faker\Generator $faker;

	protected function setUp(): void
	{
		parent::setUp();
		$this->createApplication();
		$this->transferRepository = app(TransferRepository::class);
		$this->transferCacheServiceInterface = app(TransferCacheServiceInterface::class);
		$this->faker = Factory::create();
		$this->createTransferTable();
	}

	public function testListByTeamWithSeasonWhenItemNotExist()
	{
		$response = $this->json('GET', sprintf('/en/teams/transfers/team/%s/%s', $this->faker->uuid, '2019-2020'));
		$response->assertResponseStatus(Response::HTTP_NOT_FOUND);
		$response = json_decode($response->response->getContent(), true);
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $response['code']);
	}

	public function testListByTeamWithOutSeasonWhenItemNotExist()
	{
		$response = $this->json('GET', sprintf('/en/teams/transfers/team/%s', $this->faker->uuid));
		$response->assertResponseStatus(Response::HTTP_NOT_FOUND);
		$response = json_decode($response->response->getContent(), true);
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.transfer_team_seasons_not_found'), $response['code']);
	}

	public function testListByTeamWithSeason()
	{
		$fakeTeamId = $this->faker->uuid;
		$fakeTeamName = $this->faker->name;
		$this->persistBatchDataForListByTeam($fakeTeamId, $fakeTeamName);
		/**
		 * Read from DB.
		 */
		$this->transferCacheServiceInterface->flush();
		$response = $this->json('GET', sprintf('/en/teams/transfers/team/%s/%s', $fakeTeamId, '2019-2020'));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['transfers']);
		$this->assertCount(4, $response['data']['transfers']);
		foreach ($response['data']['transfers'] as $transferItem) {
			$this->assertNotNull($transferItem['transferId']);
			$this->assertNotEmpty($transferItem['player']);
			$this->assertNotNull($transferItem['player']['id']);
			$this->assertNotNull($transferItem['player']['name']);
			$this->assertNotNull($transferItem['player']['position']);
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
		$this->assertCount(2, $response['data']['seasons']);
		/**
		 * Read from Cache.
		 */
		$this->transferRepository->drop();
		$response = $this->json('GET', sprintf('/en/teams/transfers/team/%s/%s', $fakeTeamId, '2019-2020'));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['transfers']);
		$this->assertCount(4, $response['data']['transfers']);
		foreach ($response['data']['transfers'] as $transferItem) {
			$this->assertNotNull($transferItem['transferId']);
			$this->assertNotEmpty($transferItem['player']);
			$this->assertNotNull($transferItem['player']['id']);
			$this->assertNotNull($transferItem['player']['name']);
			$this->assertNotNull($transferItem['player']['position']);
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
		$this->assertCount(2, $response['data']['seasons']);
	}

	public function testListByTeamWithOutSeason()
	{
		$fakeTeamId = $this->faker->uuid;
		$fakeTeamName = $this->faker->name;
		$this->persistBatchDataForListByTeam($fakeTeamId, $fakeTeamName);
		/**
		 * Read from DB.
		 */
		$this->transferCacheServiceInterface->flush();
		$response = $this->json('GET', sprintf('/en/teams/transfers/team/%s', $fakeTeamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['transfers']);
		$this->assertCount(5, $response['data']['transfers']);
		foreach ($response['data']['transfers'] as $transferItem) {
			$this->assertNotNull($transferItem['transferId']);
			$this->assertNotEmpty($transferItem['player']);
			$this->assertNotNull($transferItem['player']['id']);
			$this->assertNotNull($transferItem['player']['name']);
			$this->assertNotNull($transferItem['player']['position']);
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
		$this->assertCount(2, $response['data']['seasons']);
		/**
		 * Read from Cache.
		 */
		$this->transferRepository->drop();
		$response = $this->json('GET', sprintf('/en/teams/transfers/team/%s', $fakeTeamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['transfers']);
		$this->assertCount(5, $response['data']['transfers']);
		foreach ($response['data']['transfers'] as $transferItem) {
			$this->assertNotNull($transferItem['transferId']);
			$this->assertNotEmpty($transferItem['player']);
			$this->assertNotNull($transferItem['player']['id']);
			$this->assertNotNull($transferItem['player']['name']);
			$this->assertNotNull($transferItem['player']['position']);
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
		$this->assertCount(2, $response['data']['seasons']);
	}

	public function testListByPlayer()
	{
		$fakePlayerId = $this->faker->uuid;
		$fakePlayerName = $this->faker->name;
		$this->persistBatchDataForListByPlayer($fakePlayerId, $fakePlayerName);
		/**
		 * Read from DB.
		 */
		$this->transferCacheServiceInterface->flush();
		$response = $this->json('GET', sprintf('/en/teams/transfers/player/%s', $fakePlayerId));
		$response = json_decode($response->response->getContent(), true);
		foreach ($response['data'] as $transferItem) {
			$this->assertNotNull($transferItem['transferId']);
			$this->assertNotEmpty($transferItem['player']);
			$this->assertNotNull($transferItem['player']['id']);
			$this->assertNotNull($transferItem['player']['name']);
			$this->assertNotNull($transferItem['player']['position']);
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
		/**
		 * Read from Cache.
		 */
		$this->transferRepository->drop();
		$response = $this->json('GET', sprintf('/en/teams/transfers/player/%s', $fakePlayerId));
		$response = json_decode($response->response->getContent(), true);
		foreach ($response['data'] as $transferItem) {
			$this->assertNotNull($transferItem['transferId']);
			$this->assertNotEmpty($transferItem['player']);
			$this->assertNotNull($transferItem['player']['id']);
			$this->assertNotNull($transferItem['player']['name']);
			$this->assertNotNull($transferItem['player']['position']);
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
	}

	public function testListByPlayerWhenItemNotExist()
	{
		$response = $this->json('GET', sprintf('/en/teams/transfers/player/%s', $this->faker->uuid));
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
		$fakeTeamId = $this->faker->uuid;
		$fakeTeamName = $this->faker->name;
		$this->persistBatchDataForListByTeam($fakeTeamId, $fakeTeamName);
		$response = $this->json('GET', sprintf('/en/teams/transfers/team/%s/%s', $fakeTeamId, '2019-2020'));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEquals(0, $response['data']['transfers'][0]['like']);
		$this->assertEquals(0, $response['data']['transfers'][0]['dislike']);
		/**
		 * First time.
		 */
		$result = $this->json('PUT', sprintf('/en/teams/transfers/like/%s/%s',
			$response['data']['transfers'][0]['player']['id'],
			$response['data']['transfers'][0]['transferId']));
		$transferDecoded = Utility::jsonDecode($response['data']['transfers'][0]['transferId']);
		/**
		 * @var Transfer $transferItem
		 */
		$transferItem = $this->transferRepository->find([
			'playerId'  => $transferDecoded['playerId'],
			'startDate' => $transferDecoded['startDate']
		]);
		$result->assertResponseStatus(ResponseServiceInterface::STATUS_CODE_UPDATE);
		$this->assertEquals(1, $transferItem->getLike());
		$this->assertEquals(0, $transferItem->getDislike());
		/**
		 * Second time.
		 */
		$result = $this->json('PUT', sprintf('/en/teams/transfers/like/%s/%s',
			$response['data']['transfers'][0]['player']['id'],
			$response['data']['transfers'][0]['transferId']));
		$transferDecoded = Utility::jsonDecode($response['data']['transfers'][0]['transferId']);
		/**
		 * @var Transfer $transferItem
		 */
		$transferItem = $this->transferRepository->find([
			'playerId'  => $transferDecoded['playerId'],
			'startDate' => $transferDecoded['startDate']
		]);
		$result = json_decode($result->response->getContent(), true);
		$this->assertEquals(1, $transferItem->getLike());
		$this->assertEquals(0, $transferItem->getDislike());
		$this->assertNotNull($result['message']);
		$this->assertEquals(config('common.error_codes.User_is_not_allowed_to_like'), $result['code']);
		/**
		 * Third time.
		 */
		$result = $this->json('PUT', sprintf('/en/teams/transfers/dislike/%s/%s',
			$response['data']['transfers'][0]['player']['id'],
			$response['data']['transfers'][0]['transferId']));
		$transferDecoded = Utility::jsonDecode($response['data']['transfers'][0]['transferId']);
		/**
		 * @var Transfer $transferItem
		 */
		$transferItem = $this->transferRepository->find([
			'playerId'  => $transferDecoded['playerId'],
			'startDate' => $transferDecoded['startDate']
		]);
		$result->assertResponseStatus(ResponseServiceInterface::STATUS_CODE_UPDATE);
		$this->assertEquals(0, $transferItem->getLike());
		$this->assertEquals(1, $transferItem->getDislike());
	}

	protected function tearDown(): void
	{
		$this->transferCacheServiceInterface->flush();
		$this->transferRepository->drop();
	}
}