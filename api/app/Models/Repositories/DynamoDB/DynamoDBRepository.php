<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/30/2020
 * Time: 9:11 AM
 */

namespace App\Models\Repositories\DynamoDB;


use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryModelInterface;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\Result;
use Sentry\State\HubInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class DynamoDBRepository
 * @package App\Services\DynamoDB
 */
abstract class DynamoDBRepository
{
	/**
	 * @var DynamoDbClient
	 */
	protected DynamoDbClient $dynamoDbClient;

	/**
	 * @var HubInterface
	 */
	protected HubInterface $sentryHub;

	/**
	 * @var Marshaler
	 */
	protected Marshaler $marshaler;

	/**
	 * @var SerializerInterface
	 */
	protected SerializerInterface $serializer;

	/**
	 * DynamoDBRepository constructor.
	 * @param HubInterface $sentryHub
	 */
	public function __construct(HubInterface $sentryHub)
	{
		$this->dynamoDbClient = new DynamoDbClient(config('aws.dynamoDb'));
		$this->sentryHub = $sentryHub;
		$this->marshaler = new Marshaler();
		$this->serializer = app('Serializer');
	}

	/**
	 * @return string
	 */
	public static function getTableName(): string
	{
		return '';
	}

	/**
	 * @param DynamoDBRepositoryModelInterface $model
	 * @return Result
	 * @throws DynamoDBRepositoryException
	 */
	public function persist(DynamoDBRepositoryModelInterface $model = null): Result
	{
		$item = $this->marshaler->marshalJson($this->serializer->serialize($model, 'json'));
		try {
			return $this->dynamoDbClient->putItem([
				'TableName' => static::getTableName(),
				'Item' => $item
			]);
		} catch (Exception $exception) {
			throw new DynamoDBRepositoryException($exception->getMessage(), $exception->getCode(), $exception);
		}
	}

	/**
	 * @param array $criteria
	 * @param array $newValues
	 * @return Result
	 * @throws DynamoDBRepositoryException
	 */
	public function update(array $criteria, array $newValues): Result
	{
		try {
			$attrNames = array();
			$attrValues = array();
			$updateExpression = array();
			$keyCriteria = $this->marshaler->marshalJson(json_encode($criteria));
			foreach ($newValues as $key => $value) {
				$attrNames['#' . $key] = $key;
				$attrValues[':' . $key] = $value;
				$updateExpression[] = sprintf("#%s=:%s", $key, $key);
			}
			return $this->dynamoDbClient->updateItem([
				'TableName' => static::getTableName(),
				'Key' => $keyCriteria,
				'UpdateExpression' => 'set' . join(",", $updateExpression),
				'ExpressionAttributeNames' => $attrNames,
				'ExpressionAttributeValues' => $this->marshaler->marshalJson(json_encode($attrValues)),
				'ReturnValues' => 'UPDATED_NEW'
			]);
		} catch (Exception $exception) {
			throw new DynamoDBRepositoryException($exception->getMessage(), $exception->getCode(), $exception);
		}
	}

    /**
     * @param array $keys
     * @return DynamoDBRepositoryModelInterface|null
     */
    public function find(array $keys): ?DynamoDBRepositoryModelInterface
	{
		try {
			$result = $this->dynamoDbClient->getItem([
				'ConsistentRead' => true,
				'TableName' => static::getTableName(),
				'Key' => $this->marshaler->marshalJson(json_encode($keys))
			]);
			if (empty($result['Item'])) {
				return null;
			}
			return $this->deserializeResult($result);
		} catch (Exception $exception) {
			$this->sentryHub->captureException($exception);
			return null;
		}
	}

	/**
	 * @return Result|null
	 */
	public function drop(): ?Result
	{
		try {
			return $this->dynamoDbClient->deleteTable([
				'TableName' => static::getTableName()
			]);
		} catch (Exception $exception) {
			return null;
		}
	}

	/**
	 * @return Result
	 * @throws DynamoDBRepositoryException
	 */
	public function createTable(): Result
	{
		try {
			return $this->dynamoDbClient->createTable($this->schema());
		} catch (Exception $exception) {
			throw new DynamoDBRepositoryException($exception->getMessage(), $exception->getCode(), $exception);
		}
	}

	/**
	 * @return array
	 */
	public function findAll(): array
	{
		$result = $this->dynamoDbClient->scan(['TableName' => static::getTableName()]);
		if (!$result || empty($result['Items'])) {
			return array();
		}
		return $this->deserializeResult($result);
	}

	/**
	 * @return string
	 */
	public static function getFqcnModel(): string
	{
		return '';
	}

	/**
	 * @return DynamoDbClient
	 */
	public function getDynamoDbClient(): DynamoDbClient
	{
		return $this->dynamoDbClient;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected function marshalJson(array $data)
	{
		return $this->marshaler->marshalJson( json_encode( $data ) );
	}


    /**
     * @param Result $result
     * @return array|null
     */
    protected function deserializeResult(Result $result)
	{
		if (isset($result['Item'])) {
			if (empty($result['Item'])) {
				return null;
			}
			try {
				return $this->serializer->deserialize(json_encode( $this->marshaler->unmarshalItem($result['Item'])),
					static::getFqcnModel(), 'json');
			} catch (\Exception $exception) {
				$this->sentryHub->captureException($exception);
				return null;
			}
		}
		if (isset($result['Items'])) {
			if (empty($result['Items'])) {
				return array();
			}
			try {
				return array_map( function($data) {
					return $this->serializer->deserialize(json_encode($this->marshaler->unmarshalItem($data)),
						static::getFqcnModel(), 'json');
				}, $result['Items']);
			} catch (\Exception $exception) {
				$this->sentryHub->captureException($exception);
				return array();
			}
		}
	}

    /**
     * @param Result $result
     * @return array
     */
    protected function deserializePaginatedResult(Result $result): array
    {
        $items = $this->deserializeResult($result);

        if ($items === null) {
            return [];
        }

        return [
            'Items' => $items,
            'LastEvaluatedKey' => $result['LastEvaluatedKey'],
        ];
    }
}
