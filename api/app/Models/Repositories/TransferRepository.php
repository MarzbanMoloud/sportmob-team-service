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
	 * @param string $playerId
	 * @return array|null
	 */
	public function findByPlayerId(string $playerId)
	{
		try {
			$Result = $this->dynamoDbClient->query( [
				'TableName'                 => static::getTableName(),
				'KeyConditionExpression'    => 'playerId = :playerId',
				'ExpressionAttributeValues' => $this->marshalJson( [ ':playerId' => $playerId ] )
			] );
		} catch (\Exception $e) {
			$this->sentryHub->captureException( $e );
			return [];
		}

		return $this->deserializeResult( $Result );
	}

	/**
	 * @param string $playerId
	 * @return array|null
	 */
	public function findActiveTransfer(string $playerId)
	{
		try {
			$Result = $this->dynamoDbClient->query( [
				'TableName'                 => static::getTableName(),
				'KeyConditionExpression'    => 'playerId = :playerId',
				'FilterExpression'          => 'active = :active',
				'ExpressionAttributeValues' => $this->marshalJson( [
					':playerId' => $playerId,
					':active'   => true
				] )
			] );
		} catch (\Exception $e) {
			$this->sentryHub->captureException( $e );
			return [];
		}
		return $this->deserializeResult( $Result );
	}

	/**
	 * @param string $teamId
	 * @param string $season
	 * @return array
	 */
	public function findByTeamIdAndSeason(string $teamId, string $season): array
	{
		try {
			$Result =
				$this->dynamoDbClient->scan([
					'TableName'                 => static::getTableName(),
					'FilterExpression'          => '(fromTeamId = :team or toTeamId = :team) and season = :season',
					'ExpressionAttributeValues' => $this->marshalJson([
						':team'   => $teamId,
						':season' => $season
					])
				]);
		} catch (\Exception $e) {
			$this->sentryHub->captureException( $e );
			return [];
		}
		return $this->deserializeResult( $Result );
	}

	/**
	 * @param string $teamId
	 * @return array
	 */
	public function getAllSeasons(string $teamId): array
	{
		try {
			$Result =
				$this->dynamoDbClient->scan( [
					'TableName'                 => static::getTableName(),
					'FilterExpression'          => 'fromTeamId = :team or toTeamId = :team',
					'ProjectionExpression'      => 'season',
					'ExpressionAttributeValues' => $this->marshalJson( [ ':team' => $teamId ] )
				] );
		} catch (\Exception $e) {
			$this->sentryHub->captureException( $e );
			return [];
		}
		if (!$Result[ 'Items' ]) {
			return [];
		}
		$Seasons = array_map( function(array $data) {
			return $this->marshaler->unmarshalItem( $data )[ 'season' ];
		},
			$Result[ 'Items' ] );
		return array_unique( $Seasons );
	}

	/**
	 * @param string $teamId
	 * @return array
	 */
	public function findByTeamId(string $teamId): array
	{
		try {
			$Result =
				$this->dynamoDbClient->scan( [
					'TableName'                 => static::getTableName(),
					'FilterExpression'          => 'fromTeamId = :team or toTeamId = :team',
					'ExpressionAttributeValues' => $this->marshalJson( [ ':team' => $teamId ] )
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
			'TableName'             => static::getTableName(),
			'AttributeDefinitions'  => [
				[
					'AttributeName' => 'playerId',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
				[
					'AttributeName' => 'startDate',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				]
			],
			'KeySchema'             => [
				[
					'AttributeName' => 'playerId',
					'KeyType'       => DynamoDBRepositoryInterface::KEY_HASH
				],
				[
					'AttributeName' => 'startDate',
					'KeyType'       => DynamoDBRepositoryInterface::KEY_RANGE
				]
			],
			'ProvisionedThroughput' => [
				'ReadCapacityUnits'  => 1,
				'WriteCapacityUnits' => 1
			]
		];
	}
}