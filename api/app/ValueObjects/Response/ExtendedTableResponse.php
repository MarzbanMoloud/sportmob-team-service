<?php


namespace App\ValueObjects\Response;


/**
 * Class ExtendedTableResponse
 * @package App\ValueObjects\Response
 */
class ExtendedTableResponse
{
	/**
	 * @var $away TableGroupResponse[]
	 */
	private ?array $away = null;

	/**
	 * @var $home TableGroupResponse[]
	 */
	private ?array $home = null;

	/**
	 * @var $away TableGroupResponse[]
	 */
	private ?array $total = null;

	/**
	 * @param array|null $away
	 * @param array|null $home
	 * @param array|null $total
	 * @return ExtendedTableResponse
	 */
	public static function create(
		?array $away = null,
		?array $home = null,
		?array $total = null
	): ExtendedTableResponse {
		$instance = new self();
		$instance->away = $away;
		$instance->home = $home;
		$instance->total = $total;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return [
            'away' => $this->away,
            'home' => $this->home,
            'total' => $this->total,
        ];
    }
}
