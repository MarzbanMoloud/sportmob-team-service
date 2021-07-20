<?php


namespace App\Models\Repositories;


use App\Models\ReadModels\Transfer;
use App\Models\Repositories\DynamoDB\DynamoDBRepository;
use App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryInterface;


/**
 * Class TransferRepository
 * @package App\Models\Repositories
 */
class TransferRepository extends DynamoDBRepository implements DynamoDBRepositoryInterface
{
	const INDEX_PLAYER = 'IndexPlayer';
	const INDEX_PLAYER_ACTIVE_TRANSFER = 'IndexPlayerActiveTransfer';
	const INDEX_TO_TEAM = 'IndexToTeam';
	const INDEX_FROM_TEAM = 'IndexFromTeam';

	/**
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'team_service_transfers';
	}

	/**
	 * @return string
	 */
	public static function getFqcnModel(): string
	{
		return Transfer::class;
	}

	/**
	 * @param string $id
	 * @return array|null
	 */
	public function findByPlayerId(string $id)
	{
		try {
			$Result = $this->dynamoDbClient->query([
				'TableName' => static::getTableName(),
				'IndexName' => self::INDEX_PLAYER,
				'KeyConditionExpression' => 'playerId = :playerId',
				'ExpressionAttributeValues' => $this->marshalJson([
					':playerId' => $id,
				])
			]);
		} catch (\Exception $e) {
			$this->sentryHub->captureException($e);
			return [];
		}
		return $this->deserializeResult($Result);
	}

	/**
	 * @param string $id
	 * @return array|null
	 */
	public function findActiveTransfer(string $id)
	{
		try {
			$Result = $this->dynamoDbClient->query([
				'TableName' => static::getTableName(),
				'IndexName' => self::INDEX_PLAYER_ACTIVE_TRANSFER,
				'KeyConditionExpression' => 'playerId = :playerId and active = :active',
				'ExpressionAttributeValues' => $this->marshalJson([
					':playerId' => $id,
					':active' => 1
				])
			]);
		} catch (\Exception $e) {
			$this->sentryHub->captureException($e);
			return [];
		}
		return $this->deserializeResult($Result);
	}

	/**
	 * @param string $teamIndex
	 * @param string $id
	 * @param string|null $season
	 * @return array
	 */
	public function findByTeamIdAndSeason(string $teamIndex, string $id, string $season = null): array
	{
		try {
			$indexName = $teamIndex == Transfer::ATTR_TO_TEAM_ID ? self::INDEX_TO_TEAM : self::INDEX_FROM_TEAM;
			$queryParams = [
				'TableName' => static::getTableName(),
				'IndexName' => $indexName,
				'KeyConditionExpression' => "$teamIndex = :teamId",
				'ExpressionAttributeValues' => $this->marshalJson([
					':teamId' => $id,
				])
			];

			if ($season) {
				$queryParams['KeyConditionExpression'] = "$teamIndex = :teamId and season = :season";
				$queryParams['ExpressionAttributeValues'] = $this->marshalJson([
					':teamId' => $id,
					':season' => $season,
				]);
			}

			$Result = $this->dynamoDbClient->query($queryParams);
		} catch (\Exception $e) {
			$this->sentryHub->captureException($e);
			return [];
		}
		return $this->deserializeResult($Result);
	}

	/**
	 * @return array
	 */
	public function schema(): array
	{
		return [
			'TableName' => static::getTableName(),
			'AttributeDefinitions' => [
				[
					'AttributeName' => 'id',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
				[
					'AttributeName' => 'playerId',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
				[
					'AttributeName' => 'startDate',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
				[
					'AttributeName' => 'season',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
				[
					'AttributeName' => 'toTeamId',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
				[
					'AttributeName' => 'fromTeamId',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
				[
					'AttributeName' => 'active',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_NUMBER
				]
			],
			'KeySchema' => [
				[
					'AttributeName' => 'id',
					'KeyType' => DynamoDBRepositoryInterface::KEY_HASH
				]
			],
			'GlobalSecondaryIndexes' => [
				[
					'IndexName' => self::INDEX_PLAYER,
					'KeySchema' => [
						[
							'AttributeName' => 'playerId',
							'KeyType' => DynamoDBRepositoryInterface::KEY_HASH
						],
						[
							'AttributeName' => 'startDate',
							'KeyType' => DynamoDBRepositoryInterface::KEY_RANGE
						]
					],
					'Projection' => ['ProjectionType' => DynamoDBRepositoryInterface::PROJECTION_ALL],
					'ProvisionedThroughput' => [
						'ReadCapacityUnits' => 1,
						'WriteCapacityUnits' => 1
					]
				],
				[
					'IndexName' => self::INDEX_PLAYER_ACTIVE_TRANSFER,
					'KeySchema' => [
						[
							'AttributeName' => 'playerId',
							'KeyType' => DynamoDBRepositoryInterface::KEY_HASH
						],
						[
							'AttributeName' => 'active',
							'KeyType' => DynamoDBRepositoryInterface::KEY_RANGE
						]
					],
					'Projection' => ['ProjectionType' => DynamoDBRepositoryInterface::PROJECTION_ALL],
					'ProvisionedThroughput' => [
						'ReadCapacityUnits' => 1,
						'WriteCapacityUnits' => 1
					]
				],
				[
					'IndexName' => self::INDEX_TO_TEAM,
					'KeySchema' => [
						[
							'AttributeName' => 'toTeamId',
							'KeyType' => DynamoDBRepositoryInterface::KEY_HASH
						],
						[
							'AttributeName' => 'season',
							'KeyType' => DynamoDBRepositoryInterface::KEY_RANGE
						]
					],
					'Projection' => ['ProjectionType' => DynamoDBRepositoryInterface::PROJECTION_ALL],
					'ProvisionedThroughput' => [
						'ReadCapacityUnits' => 1,
						'WriteCapacityUnits' => 1
					]
				],
				[
					'IndexName' => self::INDEX_FROM_TEAM,
					'KeySchema' => [
						[
							'AttributeName' => 'fromTeamId',
							'KeyType' => DynamoDBRepositoryInterface::KEY_HASH
						],
						[
							'AttributeName' => 'season',
							'KeyType' => DynamoDBRepositoryInterface::KEY_RANGE
						]
					],
					'Projection' => ['ProjectionType' => DynamoDBRepositoryInterface::PROJECTION_ALL],
					'ProvisionedThroughput' => [
						'ReadCapacityUnits' => 1,
						'WriteCapacityUnits' => 1
					]
				],
			],
			'ProvisionedThroughput' => [
				'ReadCapacityUnits' => 1,
				'WriteCapacityUnits' => 1
			]
		];
	}
}