<?php


namespace App\Models\Repositories;


use App\Models\ReadModels\Trophy;
use App\Models\Repositories\DynamoDB\DynamoDBRepository;
use App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryInterface;


/**
 * Class TrophyRepository
 * @package App\Models\Repositories
 */
class TrophyRepository extends DynamoDBRepository implements DynamoDBRepositoryInterface
{
	public const TROPHY_TEAM_INDEX = 'TrophyTeamIndex';
	public const TROPHY_COMPETITION_TOURNAMENT_INDEX = 'TrophyCompetitionTournamentIndex';
	public const TROPHY_TOURNAMENT_INDEX = 'TrophyTournamentIndex';

	/**
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'team_service_trophies';
	}

	/**
	 * @return string
	 */
	public static function getFqcnModel(): string
	{
		return Trophy::class;
	}

	/**
	 * @param string $teamId
	 * @return Trophy[]|[]
	 */
	public function findByTeamId(string $teamId): array
	{
		try {
			$Result = $this->dynamoDbClient->query( [
				'TableName'                 => static::getTableName(),
				'IndexName'                 => self::TROPHY_TEAM_INDEX,
				'KeyConditionExpression'    => 'teamId = :teamId',
				'ExpressionAttributeValues' => $this->marshalJson([':teamId' => $teamId])
			] );
		} catch (\Exception $e) {
			$this->sentryHub->captureException( $e );
			return [];
		}
		return $this->deserializeResult( $Result );
	}

	/**
	 * @param string $tournamentId
	 * @return array
	 */
	public function findByTournamentId(string $tournamentId): array
	{
		try {
			$Result = $this->dynamoDbClient->query( [
				'TableName'                 => static::getTableName(),
				'IndexName'                 => self::TROPHY_TOURNAMENT_INDEX,
				'KeyConditionExpression'    => 'tournamentId = :tournamentId',
				'ExpressionAttributeValues' => $this->marshalJson([':tournamentId' => $tournamentId])
			] );
		} catch (\Exception $e) {
			$this->sentryHub->captureException( $e );
			return [];
		}
		return $this->deserializeResult( $Result );
	}

	/**
	 * @param string $competitionId
	 * @return array|null
	 */
	public function findByCompetition(string $competitionId)
	{
		try {
			$Result = $this->dynamoDbClient->query( [
				'TableName'                 => static::getTableName(),
				'KeyConditionExpression'    => 'competitionId = :competition',
				'ExpressionAttributeValues' => $this->marshalJson( [ ':competition' => $competitionId ] )
			] );
		} catch (\Exception $e) {
			$this->sentryHub->captureException( $e );
			return [];
		}
		return $this->deserializeResult( $Result );
	}

	/**
	 * @param string $competitionId
	 * @param string $tournamentId
	 * @param string $teamId
	 * @return array|null
	 */
	public function findExcludesByCompetitionTournament(string $competitionId, string $tournamentId, string $teamId)
	{
		try {
			$Result = $this->dynamoDbClient->query( [
				'TableName'                 => static::getTableName(),
				'KeyConditionExpression'    => 'competitionId = :competition and begins_with(belongTo,:tournament)',
				'FilterExpression'          => 'teamId <> :team',
				'ExpressionAttributeValues' => $this->marshalJson( [
					':competition' => $competitionId,
					':tournament'  => $tournamentId,
					':team'        => $teamId
				] )
			] );
		} catch (\Exception $e) {
			$this->sentryHub->captureException( $e );
			return [];
		}
		return $this->deserializeResult( $Result );
	}

	/**
	 * @return array
	 */
	public function schema(): array
	{
		return [
			'TableName'              => static::getTableName(),
			'AttributeDefinitions'   => [
				[
					'AttributeName' => 'competitionId',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
				[
					'AttributeName' => 'belongTo',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
				[
					'AttributeName' => 'tournamentId',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
				[
					'AttributeName' => 'teamId',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				]
			],
			'KeySchema'              => [
				[
					'AttributeName' => 'competitionId',
					'KeyType'       => DynamoDBRepositoryInterface::KEY_HASH
				],
				[
					'AttributeName' => 'belongTo',
					'KeyType'       => DynamoDBRepositoryInterface::KEY_RANGE
				]
			],
			'GlobalSecondaryIndexes' => [
				[
					'IndexName'             => self::TROPHY_TEAM_INDEX,
					'KeySchema'             => [
						[
							'AttributeName' => 'teamId',
							'KeyType'       => DynamoDBRepositoryInterface::KEY_HASH
						]
					],
					'Projection'            => [ 'ProjectionType' => DynamoDBRepositoryInterface::PROJECTION_ALL ],
					'ProvisionedThroughput' => [
						'ReadCapacityUnits'  => 1,
						'WriteCapacityUnits' => 1
					]
				],
				[
					'IndexName'             => self::TROPHY_TOURNAMENT_INDEX,
					'KeySchema'             => [
						[
							'AttributeName' => 'tournamentId',
							'KeyType'       => DynamoDBRepositoryInterface::KEY_HASH
						]
					],
					'Projection'            => [ 'ProjectionType' => DynamoDBRepositoryInterface::PROJECTION_ALL ],
					'ProvisionedThroughput' => [
						'ReadCapacityUnits'  => 1,
						'WriteCapacityUnits' => 1
					]
				],
				[
					'IndexName'             => self::TROPHY_COMPETITION_TOURNAMENT_INDEX,
					'KeySchema'             => [
						[
							'AttributeName' => 'competitionId',
							'KeyType'       => DynamoDBRepositoryInterface::KEY_HASH
						],
						[
							'AttributeName' => 'tournamentId',
							'KeyType'       => DynamoDBRepositoryInterface::KEY_RANGE
						]
					],
					'Projection'            => [ 'ProjectionType' => DynamoDBRepositoryInterface::PROJECTION_ALL ],
					'ProvisionedThroughput' => [
						'ReadCapacityUnits'  => 1,
						'WriteCapacityUnits' => 1
					]
				]
			],
			'ProvisionedThroughput'  => [
				'ReadCapacityUnits'  => 1,
				'WriteCapacityUnits' => 1
			]
		];
	}
}