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
	const INDEX_PERSON = 'IndexPerson';
	const INDEX_TO_TEAM_ID = 'IndexToTeamId';
	const INDEX_FROM_TEAM_ID = 'IndexFromTeamId';

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
	public function findByPersonId(string $id)
	{
		try {
			$Result = $this->dynamoDbClient->query([
				'TableName' => static::getTableName(),
				'IndexName' => self::INDEX_PERSON,
				'KeyConditionExpression' => '#personId = :personId',
				'FilterExpression'          => '#type <> :type',
				'ExpressionAttributeValues' => $this->marshalJson([
					':personId' => $id,
					':type' => Transfer::TRANSFER_TYPE_LOAN_BACK,
				]),
				'ExpressionAttributeNames' => [
					'#type' => 'type',
					'#personId' => 'personId'
				]
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
	public function findAllByTeamIdAndSeason(string $teamIndex, string $id, string $season = null): array
	{
		try {
			$indexName = $teamIndex == Transfer::ATTR_TO_TEAM_ID ? self::INDEX_TO_TEAM_ID : self::INDEX_FROM_TEAM_ID;

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

			$results = [];

			do {
				$result = $this->dynamoDbClient->query($queryParams);
				$results[] = $this->deserializeResult($result);
			} while ($queryParams['ExclusiveStartKey'] = $result['LastEvaluatedKey']);

			return call_user_func_array('array_merge', $results);

		} catch (\Exception $e) {
			$this->sentryHub->captureException($e);
			return [];
		}
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
					'AttributeName' => 'personId',
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
					'IndexName' => self::INDEX_PERSON,
					'KeySchema' => [
						[
							'AttributeName' => 'personId',
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
					'IndexName' => self::INDEX_TO_TEAM_ID,
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
					'IndexName' => self::INDEX_FROM_TEAM_ID,
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