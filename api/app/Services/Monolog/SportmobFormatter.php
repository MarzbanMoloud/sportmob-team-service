<?php

namespace App\Services\Monolog;

use Elastica\Document;
use Monolog\Formatter\ElasticaFormatter;


/**
 * Class SportmobFormatter
 * @package App\Services\Monolog
 */
class SportmobFormatter extends ElasticaFormatter
{

	/**
	 * SportmobFormatter constructor.
	 * @param string $index
	 * @param string|null $type
	 */
	public function __construct(string $index, ?string $type)
	{
		parent::__construct($index, $type);
	}

	/**
	 * @param array $record
	 * @return array|bool|Document|float|int|mixed|string|null
	 */
	public function format(array $record)
	{
		/**
		 * @var Document $document
		 */
		$document = parent::format($record);
		$document->setData($document->getData() + ['origin' => env('APP_NAME')]);
		return $document;
	}

	/**
	 * @param array $records
	 * @return array|mixed|void
	 */
	public function formatBatch(array $records)
	{
		parent::formatBatch($records);
	}
}