<?php


namespace App\Models\Repositories;


use App\Models\ReadModels\Team;
use App\Models\Repositories\DynamoDB\DynamoDBRepository;
use App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryInterface;


/**
 * Class TeamRepository
 * @package App\Models\Repositories
 */
class TeamRepository extends DynamoDBRepository implements DynamoDBRepositoryInterface
{
	/**
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'team_service_teams';
	}

	/**
	 * @return string
	 */
	public static function getFqcnModel(): string
	{
		return Team::class;
	}

	/**
	 * @return array
	 */
	public function schema(): array
	{
		return [
			'TableName'             => self::getTableName(),
			'AttributeDefinitions'  => [
				[
					'AttributeName' => 'id',
					'AttributeType' => DynamoDBRepositoryInterface::TYPE_STRING
				],
			],
			'KeySchema'             => [
				[
					'AttributeName' => 'id',
					'KeyType'       => DynamoDBRepositoryInterface::KEY_HASH
				],
			],
			'ProvisionedThroughput' => [
				'ReadCapacityUnits'  => 1,
				'WriteCapacityUnits' => 1
			]
		];
	}
}