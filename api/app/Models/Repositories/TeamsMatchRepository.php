<?php


namespace App\Models\Repositories;


use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\DynamoDB\DynamoDBRepository;
use App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryInterface;


/**
 * Class TeamsMatchRepository
 * @package App\Models\Repositories
 */
class TeamsMatchRepository extends DynamoDBRepository implements DynamoDBRepositoryInterface
{
	public const TEAM_INDEX = 'TeamIndex';
	public const OPPONENT_INDEX = 'OpponentIndex';
	public const MATCH_INDEX = 'MatchIndex';

	/**
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'team_service_teams_match';
	}

	/**
	 * @return string
	 */
	public static function getFqcnModel(): string
	{
		return TeamsMatch::class;
	}

	/**
	 * @param string $id
	 * @param string $status
	 * @return array|null
	 */
	public function findTeamsMatchByTeamId(string $id, string $status)
	{
		try {
			$Result = $this->dynamoDbClient->query( [
				'ScanIndexForward'          => false,
				'TableName'                 => static::getTableName(),
				'IndexName'                 => self::TEAM_INDEX,
				'KeyConditionExpression'    => 'teamId = :teamId and begins_with(sortKey,:status)',
				'ExpressionAttributeValues' => $this->marshalJson([
					':teamId' => $id,
					':status' => $status
				])
			] );
		} catch (\Exception $e) {
			$this->sentryHub->captureException( $e );
			return [];
		}
		return $this->deserializeResult( $Result );
	}

	/**
	 * @param string $id
	 * @return array|null
	 */
	public function findTeamsMatchByOpponentId(string $id)
	{
		try {
			$Result = $this->dynamoDbClient->query( [
				'ScanIndexForward'          => false,
				'TableName'                 => static::getTableName(),
				'IndexName'                 => self::OPPONENT_INDEX,
				'KeyConditionExpression'    => 'opponentId = :opponentId',
				'ExpressionAttributeValues' => $this->marshalJson([
					':opponentId' => $id,
				])
			] );
		} catch (\Exception $e) {
			$this->sentryHub->captureException( $e );
			return [];
		}
		return $this->deserializeResult( $Result );
	}

	/**
	 * @param string $matchId
	 * @return array
	 */
	public function findTeamsMatchByMatchId(string $matchId): array
	{
		try {
			$Result = $this->dynamoDbClient->query([
				'TableName' => $this->getTableName(),
				'KeyConditionExpression' => 'matchId = :matchId',
				'ExpressionAttributeValues' => $this->marshalJson([
					":matchId" => $matchId
				]),
			]);
		} catch (\Exception $e) {
			$this->sentryHub->captureException( $e );
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
			'TableName'             => static::getTableName(),
			'AttributeDefinitions'  => [
				[
					'AttributeName' => 'matchId',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
				[
					'AttributeName' => 'teamId',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
				[
					'AttributeName' => 'sortKey',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
				[
					'AttributeName' => 'opponentId',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				]
			],
			'KeySchema'             => [
				[
					'AttributeName' => 'matchId',
					'KeyType'       => DynamoDBRepositoryInterface::KEY_HASH
				],
				[
					'AttributeName' => 'teamId',
					'KeyType'       => DynamoDBRepositoryInterface::KEY_RANGE
				]
			],
			'GlobalSecondaryIndexes' => [
				[
					'IndexName'             => self::TEAM_INDEX,
					'KeySchema'             => [
						[
							'AttributeName' => 'teamId',
							'KeyType'       => DynamoDBRepositoryInterface::KEY_HASH
						],
						[
							'AttributeName' => 'sortKey',
							'KeyType'       => DynamoDBRepositoryInterface::KEY_RANGE
						]
					],
					'Projection'            => [ 'ProjectionType' => DynamoDBRepositoryInterface::PROJECTION_ALL ],
					'ProvisionedThroughput' => [
						'ReadCapacityUnits'  => 1,
						'WriteCapacityUnits' => 1
					]
				],
				[
					'IndexName'             => self::OPPONENT_INDEX,
					'KeySchema'             => [
						[
							'AttributeName' => 'opponentId',
							'KeyType'       => DynamoDBRepositoryInterface::KEY_HASH
						]
					],
					'Projection'            => [ 'ProjectionType' => DynamoDBRepositoryInterface::PROJECTION_ALL ],
					'ProvisionedThroughput' => [
						'ReadCapacityUnits'  => 1,
						'WriteCapacityUnits' => 1
					]
				],
			],
			'ProvisionedThroughput' => [
				'ReadCapacityUnits'  => 1,
				'WriteCapacityUnits' => 1
			]
		];
	}
}