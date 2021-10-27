<?php


namespace App\ValueObjects\Response;


use DateTimeImmutable;


/**
 * Class PersonResponse
 * @package App\ValueObjects\Response
 */
class PersonResponse
{
	private string $id;
	private ?NameResponse $name = null;
	private ?string $position = null;
	private ?int $weight = null;
	private ?int $height = null;
	private ?DateTimeImmutable $birthDate = null;

	/**
	 * @param string $id
	 * @param NameResponse|null $name
	 * @param string|null $position
	 * @param int|null $weight
	 * @param int|null $height
	 * @param DateTimeImmutable|null $birthDate
	 * @return PersonResponse
	 */
	public static function create(
		string $id,
		?NameResponse $name = null,
		?string $position = null,
		?int $weight = null,
		?int $height = null,
		?DateTimeImmutable $birthDate = null
	): PersonResponse {
		$instance = new self();
		$instance->id = $id;
		$instance->name = $name;
		$instance->position = $position;
		$instance->weight = $weight;
		$instance->height = $height;
		$instance->birthDate = $birthDate;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
            'id' => $this->id,
            'name' => $this->name ? $this->name->toArray() : null,
            'position' => $this->position,
            'weight' => $this->weight,
            'height' => $this->height,
            'birthDate' => $this->birthDate ? $this->birthDate->format('Y-m-d') : null,
        ]);
    }
}
