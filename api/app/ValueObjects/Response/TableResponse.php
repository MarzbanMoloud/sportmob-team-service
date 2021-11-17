<?php


namespace App\ValueObjects\Response;


/**
 * Class TableResponse
 * @package App\ValueObjects\Response
 */
class TableResponse
{
	private ?ExtendedTableResponse $tables = null;
	/**
	 * @var $playOffs PlayOffItemResponse[]
	 */
	private ?array $playOffs = null;

	/**
	 * @param ExtendedTableResponse|null $tables
	 * @param array|null $playOffs
	 * @return TableResponse
	 */
	public static function create(
		?ExtendedTableResponse $tables = null,
		?array $playOffs = null
	): TableResponse {
		$instance = new self();
		$instance->tables = $tables;
		$instance->playOffs = $playOffs;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
            'tables' => $this->tables ? $this->tables->toArray() : null,
            'playOffs' => $this->playOffs,
        ]);
    }
}
