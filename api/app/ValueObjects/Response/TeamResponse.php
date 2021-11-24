<?php


namespace App\ValueObjects\Response;


/**
 * Class TeamResponse
 * @package App\ValueObjects\Response
 */
class TeamResponse
{
	private string $id;
	private ?NameResponse $teamName = null;
	private ?string $code = null;
	private ?string $city = null;
	private ?string $founded = null;
	private ?string $gender = null;
	private ?string $type = null;

	/**
	 * @param string $id
	 * @param NameResponse|null $teamName
	 * @param string|null $code
	 * @param string|null $city
	 * @param string|null $founded
	 * @param string|null $gender
	 * @param string|null $type
	 * @return TeamResponse
	 */
	public static function create(
		string $id,
		?NameResponse $teamName = null,
		?string $code = null,
		?string $city = null,
		?string $founded = null,
		?string $gender = null,
		?string $type = null
	): TeamResponse {
		$instance = new self();
		$instance->id = $id;
		$instance->teamName = $teamName;
		$instance->code = $code;
		$instance->city = $city;
		$instance->founded = $founded;
		$instance->gender = $gender;
		$instance->type = $type;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
            'id' => $this->id,
            'name' => $this->teamName ? $this->teamName->toArray() : null,
            'code' => $this->code,
            'city' => $this->city,
            'founded' => $this->founded,
            'gender' => $this->gender,
            'type' => $this->type,
        ]);
    }
}
