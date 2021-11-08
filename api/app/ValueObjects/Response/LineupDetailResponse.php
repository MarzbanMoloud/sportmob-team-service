<?php


namespace App\ValueObjects\Response;


/**
 * Class LineupDetailResponse
 * @package App\ValueObjects\Response
 */
class LineupDetailResponse
{
    private string $formation;
    private array $color;
    /**
     * @var PersonResponse[]
     */
    private array $coaches;
    /**
     * @var LineupPlayerResponse[]
     */
    private array $lineup;
    /**
     * @var LineupPlayerResponse[]
     */
    private array $bench;
    /**
     * @var LineupPlayerResponse[]
     */
    private array $willNotPlay;

	/**
	 * @param string $formation
	 * @param array $color
	 * @param array $coaches
	 * @param array $lineup
	 * @param array $bench
	 * @param array $willNotPlay
	 * @return LineupDetailResponse
	 */
    public static function create(
		string $formation,
		array $color,
		array $coaches,
		array $lineup,
		array $bench,
		array $willNotPlay
	): LineupDetailResponse {
        $instance = new self();
        $instance->formation = $formation;
        $instance->color = $color;
        $instance->coaches = $coaches;
        $instance->lineup = $lineup;
        $instance->bench = $bench;
        $instance->willNotPlay = $willNotPlay;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'formation' => $this->formation,
            'color' => $this->color,
            'coaches' => $this->coaches,
            'lineup' => $this->lineup,
            'bench' => $this->bench,
            'willNotPlay' => $this->willNotPlay,
        ]);
    }
}
