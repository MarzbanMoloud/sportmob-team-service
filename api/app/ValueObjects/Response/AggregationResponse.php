<?php


namespace App\ValueObjects\Response;


/**
 * Class AggregationResponse
 * @package App\ValueObjects\Response
 */
class AggregationResponse
{
    private ?int $draw = null;
    private ?int $homeWins = null;
    private ?int $awayWins = null;

	/**
	 * @param int|null $draw
	 * @param int|null $homeWins
	 * @param int|null $awayWins
	 * @return AggregationResponse
	 */
    public static function create(
		?int $draw = null,
		?int $homeWins = null,
		?int $awayWins = null
	): AggregationResponse {
        $instance = new self();
        $instance->draw = $draw;
        $instance->homeWins = $homeWins;
        $instance->awayWins = $awayWins;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'draw' => $this->draw,
            'homeWins' => $this->homeWins,
            'awayWins' => $this->awayWins,
        ];
    }
}
