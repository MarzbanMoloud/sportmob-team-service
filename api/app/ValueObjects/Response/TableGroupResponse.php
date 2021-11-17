<?php


namespace App\ValueObjects\Response;


/**
 * Class TableGroupResponse
 * @package App\ValueObjects\Response
 */
class TableGroupResponse
{
	private ?StageResponse $stage = null;
	/**
	 * @var TeamTableItemResponse[]
	 */
	private ?array $teamTableItems = null;

	/**
	 * @param StageResponse|null $stage
	 * @param array|null $teamTableItems
	 * @return TableGroupResponse
	 */
	public static function create(
		?StageResponse $stage = null,
		?array $teamTableItems = null
	): TableGroupResponse {
		$instance = new self();
		$instance->stage = $stage;
		$instance->teamTableItems = $teamTableItems;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
            'stage' => $this->stage ? $this->stage->toArray() : null,
            'teamTableItems' => $this->teamTableItems ? $this->teamTableItems->toArray() : null
        ]);
    }
}
