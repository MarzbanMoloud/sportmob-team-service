<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/30/2020
 * Time: 9:05 AM
 */

namespace App\Models\Repositories\DynamoDB\Interfaces;


use Aws\Result;


/**
 * Interface DynamoDBRepositoryInterface
 * @package App\Services\DynamoDB\Interfaces
 */
interface DynamoDBRepositoryInterface
{
	public const TYPE_STRING = 'S';
	public const TYPE_NUMBER = 'N';
	public const TYPE_BINARY  = 'B';
	public const KEY_HASH = 'HASH';
	public const KEY_RANGE = 'RANGE';
	public const PROJECTION_ALL = 'ALL';

	/**
	 * @return string
	 */
	public static function getTableName(): string;

	/**
	 * @return string
	 */
	public static function getFqcnModel(): string;

	/**
	 * @param DynamoDBRepositoryModelInterface $model
	 * @return Result
	 */
	public function persist(DynamoDBRepositoryModelInterface $model): Result;

	/**
	 * @param array $criteria
	 * @param array $newValues
	 * @return Result
	 */
	public function update(array $criteria, array $newValues): Result;

	/**
	 * @param array $keys
	 * @return DynamoDBRepositoryModelInterface|null
	 */
	public function find(array $keys): ?DynamoDBRepositoryModelInterface;

	/**
	 * @return Result|null
	 */
	public function drop(): ?Result;

	/**
	 * @return array
	 */
	public function schema(): array;

	/**
	 * @return Result
	 */
	public function createTable(): Result;

	/**
	 * @return DynamoDBRepositoryModelInterface[]|[]
	 */
	public function findAll(): array;
}
