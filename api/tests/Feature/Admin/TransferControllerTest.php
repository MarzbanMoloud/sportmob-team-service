<?php


namespace Tests\Feature\Admin;


use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\Utilities\Utility;
use DateTimeInterface;
use Faker\Factory;
use Illuminate\Http\Response;
use TestCase;
use Tests\Traits\TransferRepositoryTestTrait;


/**
 * Class TransferControllerTest
 * @package Tests\Feature\Admin
 */
class TransferControllerTest extends TestCase
{
	use TransferRepositoryTestTrait;

	private TransferRepository $transferRepository;
	private TransferCacheServiceInterface $transferCacheService;
	private \Faker\Generator $faker;

	protected function setUp(): void
	{
		parent::setUp();
		$this->createApplication();
		$this->faker = Factory::create();
		$this->transferRepository = app(TransferRepository::class);
		$this->transferCacheService = app(TransferCacheServiceInterface::class);
		$this->createTransferTable();
	}

	public function testIndex()
	{
		$fakePlayerId = $this->faker->uuid;
		$fakePlayerName = $this->faker->name;
		$this->persistBatchDataForListByPlayer($fakePlayerId, $fakePlayerName);
		/** Read from DB */
		$this->transferCacheService->flush();
		$response = $this->json('GET', sprintf('/admin/transfers/players/%s', $fakePlayerId));
		$response = json_decode($response->response->getContent(), true);
		foreach ($response['data'] as $transferItem) {
			$this->assertNotNull($transferItem['id']);
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
			$this->assertNotNull($transferItem['season']);
		}
		/** Read from Cache */
		$this->transferRepository->drop();
		$response = $this->json('GET', sprintf('/admin/transfers/players/%s', $fakePlayerId));
		$response = json_decode($response->response->getContent(), true);
		foreach ($response['data'] as $transferItem) {
			$this->assertNotNull($transferItem['id']);
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
			$this->assertNotNull($transferItem['season']);
		}
	}

	public function testIndexWhenItemNotExist()
	{
		$response = $this->json('GET', sprintf('/admin/transfers/players/%s', $this->faker->uuid));
		$response->assertResponseStatus(Response::HTTP_NOT_FOUND);
		$response = json_decode($response->response->getContent(), true);
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $response['code']);
	}

	public function testUpdate()
	{
		$fakePlayerId = $this->faker->uuid;
		$fakePlayerName = $this->faker->name;
		$this->persistBatchDataForListByPlayer($fakePlayerId, $fakePlayerName);
		$transferItem = $this->transferRepository->findByPlayerId($fakePlayerId);
		$transferId = base64_encode(sprintf('%s#%s', $transferItem[0]->getPlayerId(), $transferItem[0]->getStartDate()->format(DateTimeInterface::ATOM)));
		$response = $this->put('/admin/transfers/players/' . $transferId, [
			'marketValue' => '12.02$',
			'announcedDate' => 1612693084,
			'contractDate' => 1612693093,
		]);
		$response->assertResponseStatus(ResponseServiceInterface::STATUS_CODE_UPDATE);
		/** @var Transfer $updatedItem */
		$updatedItem = $this->transferRepository->find([
			'playerId' => $transferItem[0]->getPlayerId(),
			'startDate' => $transferItem[0]->getStartDate()->format(DateTimeInterface::ATOM)
		]);
		$this->assertEquals('12.02$', $updatedItem->getMarketValue());
		$this->assertEquals(new \DateTimeImmutable('2021-02-07 10:18:04.0 +00:00'), $updatedItem->getAnnouncedDate());
		$this->assertEquals(new \DateTimeImmutable('2021-02-07 10:18:13.0 +00:00'), $updatedItem->getContractDate());
	}

	public function testUpdateWithValidOrInvalidFields()
	{
		$fakePlayerId = $this->faker->uuid;
		$fakePlayerName = $this->faker->name;
		$this->persistBatchDataForListByPlayer($fakePlayerId, $fakePlayerName);
		$transferItem = $this->transferRepository->findByPlayerId($fakePlayerId);
		$transferId = base64_encode(sprintf('%s#%s', $transferItem[0]->getPlayerId(), $transferItem[0]->getStartDate()->format(DateTimeInterface::ATOM)));
		$validMarketValues = ['12.33$', '1233', '12.33'];
		foreach ($validMarketValues as $value) {
			$response = $this->put('/admin/transfers/players/' . $transferId, [
				'marketValue' => $value,
				'announcedDate' => 1612693084,
				'contractDate' => 1612693093,
			]);
			$response->assertResponseStatus(ResponseServiceInterface::STATUS_CODE_UPDATE);
		}
		$invalidMarketValues = ['12.33#', '12.33&', '12.33@', '12/33'];
		foreach ($invalidMarketValues as $value) {
			$response = $this->put('/admin/transfers/players/' . $transferId, [
				'marketValue' => $value,
				'announcedDate' => 1612693084,
				'contractDate' => 1612693093,
			]);
			$response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
			$response = json_decode($response->response->getContent(), true);
			$this->assertNotNull($response['message']);
			$this->assertEquals(config('common.error_codes.validation_failed'), $response['code']);
		}
	}

	public function testUpdateWhenItemNotExist()
	{
		$transferId = base64_encode(sprintf('%s#%s', $this->faker->uuid, (new \DateTimeImmutable())->format(DateTimeInterface::ATOM)));
		$response = $this->put('/admin/transfers/players/' . $transferId, [
			'marketValue' => '12.00$',
			'announcedDate' => 1612693084,
			'contractDate' => 1612693084,
		]);
		$response = json_decode($response->response->getContent(), true);
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $response['code']);
	}

	protected function tearDown(): void
	{
		$this->transferCacheService->flush();
		$this->transferRepository->drop();
	}
}