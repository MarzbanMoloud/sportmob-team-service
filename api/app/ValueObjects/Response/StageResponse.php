<?php


namespace App\ValueObjects\Response;


/**
 * Class StageResponse
 * @package App\ValueObjects\Response
 */
class StageResponse
{
	private string $id;
	private ?string $name = null;

	/**
	 * @param string $id
	 * @param string|null $name
	 * @return StageResponse
	 */
	public static function create(
		string $id,
		?string $name = null
	): StageResponse {
		$instance = new self();
		$instance->id = $id;
		$instance->name = $name;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
        ]);
    }
}
