<?php


namespace App\ValueObjects\Response;


/**
 * Class TopStatPersonResponse
 * @package App\ValueObjects\Response
 */
class TopStatPersonResponse
{
	private PersonResponse $person;
	private ?StatItemResponse $stat = null;

	/**
	 * @param PersonResponse $person
	 * @param StatItemResponse|null $stat
	 * @return TopStatPersonResponse
	 */
    public static function create(
		PersonResponse $person,
		?StatItemResponse $stat = null
    ): TopStatPersonResponse {
        $instance = new self();
        $instance->person = $person;
        $instance->stat = $stat;
        return $instance;
    }

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return array_filter([
			'person' => $this->person->toArray(),
			'stat' => $this->stat ? $this->stat->toArray() : null,
		]);
	}
}
