<?php


namespace Tests\Feature\Admin;


use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\Utilities\Utility;
use DateTimeInterface;
use Faker\Factory;
use Illuminate\Http\Response;
use Symfony\Component\Serializer\SerializerInterface;
use TestCase;
use Tests\Traits\TeamRepositoryTestTrait;
use Tests\Traits\TransferRepositoryTestTrait;


/**
 * Class TransferControllerTest
 * @package Tests\Feature\Admin
 */
class TransferControllerTest extends TestCase
{
	use TransferRepositoryTestTrait, TeamRepositoryTestTrait;

	private TransferRepository $transferRepository;
	private TransferCacheServiceInterface $transferCacheService;
	private \Faker\Generator $faker;
	private SerializerInterface $serializer;
	private TeamRepository $teamRepository;

	protected function setUp(): void
	{
		parent::setUp();
		$this->createApplication();
		$this->faker = Factory::create();
		$this->transferRepository = app(TransferRepository::class);
		$this->teamRepository = app(TeamRepository::class);
		$this->transferCacheService = app(TransferCacheServiceInterface::class);
		$this->serializer = app(SerializerInterface::class);
		$this->createTransferTable();
		$this->createTeamTable();
	}

	public function testIndex()
	{
		$fakePlayerId = $this->faker->uuid;
		$this->persistBatchDataForPerson($fakePlayerId);
		/** Read from DB */
		$this->transferCacheService->flush();
		$response = $this->json('GET', sprintf('/admin/persons/%s/transfers', $fakePlayerId));
		$response = json_decode($response->response->getContent(), true);
		foreach ($response['data'] as $key => $transferItem) {
			$this->assertNotNull($transferItem['id']);
			$this->assertNotEmpty($transferItem['person']);
			$this->assertNotNull($transferItem['person']['id']);
			$this->assertNull($transferItem['person']['name']);
			$this->assertNotEmpty($transferItem['team']);
			$this->assertCount(2, $transferItem['team']);
			$this->assertNotEmpty($transferItem['team']['to']);
			$this->assertNotNull($transferItem['team']['to']['id']);
			$this->assertNotNull($transferItem['team']['to']['name']);
			$this->assertNotEmpty($transferItem['team']['from']);
			if ($key != 0) {
				$this->assertNotNull($transferItem['team']['from']['name']);
				$this->assertNotNull($transferItem['team']['from']['id']);
			}
			$this->assertNull($transferItem['marketValue']);
			$this->assertNotNull($transferItem['startDate']);
			$this->assertNull($transferItem['announcedDate']);
			$this->assertNull($transferItem['contractDate']);
			$this->assertNotNull($transferItem['type']);
			$this->assertNotNull($transferItem['season']);
		}
		/** Read from Cache */
		$this->transferRepository->drop();
		$response = $this->json('GET', sprintf('/admin/persons/%s/transfers', $fakePlayerId));
		$response = json_decode($response->response->getContent(), true);
		foreach ($response['data'] as $key => $transferItem) {
			$this->assertNotNull($transferItem['id']);
			$this->assertNotEmpty($transferItem['person']);
			$this->assertNotNull($transferItem['person']['id']);
			$this->assertNull($transferItem['person']['name']);
			$this->assertNotEmpty($transferItem['team']);
			$this->assertCount(2, $transferItem['team']);
			$this->assertNotEmpty($transferItem['team']['to']);
			$this->assertNotNull($transferItem['team']['to']['id']);
			$this->assertNotNull($transferItem['team']['to']['name']);
			$this->assertNotEmpty($transferItem['team']['from']);
			if ($key != 0) {
				$this->assertNotNull($transferItem['team']['from']['name']);
				$this->assertNotNull($transferItem['team']['from']['id']);
			}
			$this->assertNull($transferItem['marketValue']);
			$this->assertNotNull($transferItem['startDate']);
			$this->assertNull($transferItem['announcedDate']);
			$this->assertNull($transferItem['contractDate']);
			$this->assertNotNull($transferItem['type']);
			$this->assertNotNull($transferItem['season']);
		}
	}

	public function testIndexWhenItemNotExist()
	{
		$response = $this->json('GET', sprintf('/admin/persons/%s/transfers', $this->faker->uuid));
		$response->assertResponseStatus(Response::HTTP_NOT_FOUND);
		$response = json_decode($response->response->getContent(), true);
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $response['code']);
	}

	public function testUpdate()
	{
		$fakePersonId = $this->faker->uuid;
		$this->persistBatchDataForPerson($fakePersonId);
		$transferItem = $this->transferRepository->findByPersonId($fakePersonId);
		$response = $this->put('/admin/persons/transfers/' . $transferItem[0]->getId(), [
			'marketValue' => '12.02$',
			'announcedDate' => 1612693084,
			'contractDate' => 1612693093,
		]);
		$response->assertResponseStatus(ResponseServiceInterface::STATUS_CODE_UPDATE);
		/** @var Transfer $updatedItem */
		$updatedItem = $this->transferRepository->find([
			'id' => $transferItem[0]->getId()
		]);
		$this->assertEquals('12.02$', $updatedItem->getMarketValue());
		$this->assertEquals(new \DateTimeImmutable('2021-02-07 10:18:04.0 +00:00'), $updatedItem->getAnnouncedDate());
		$this->assertEquals(new \DateTimeImmutable('2021-02-07 10:18:13.0 +00:00'), $updatedItem->getContractDate());
	}

	public function testUpdateWithValidOrInvalidFields()
	{
		$fakePersonId = $this->faker->uuid;
		$this->persistBatchDataForPerson($fakePersonId);
		$transferItem = $this->transferRepository->findByPersonId($fakePersonId);
		$validMarketValues = ['12.33$', '1233', '12.33'];
		foreach ($validMarketValues as $value) {
			$response = $this->put('/admin/persons/transfers/' . $transferItem[0]->getId(), [
				'marketValue' => $value,
				'announcedDate' => 1612693084,
				'contractDate' => 1612693093,
			]);
			$response->assertResponseStatus(ResponseServiceInterface::STATUS_CODE_UPDATE);
		}
		$invalidMarketValues = ['12.33#', '12.33&', '12.33@', '12/33'];
		foreach ($invalidMarketValues as $value) {
			$response = $this->put('/admin/persons/transfers/' . $transferItem[0]->getId(), [
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
		$response = $this->put('/admin/persons/transfers/' . $this->faker->uuid, [
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