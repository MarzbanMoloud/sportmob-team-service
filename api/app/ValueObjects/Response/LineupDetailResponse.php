<?php


namespace App\ValueObjects\Response;


/**
 * Class LineupDetailResponse
 * @package App\ValueObjects\Response
 */
class LineupDetailResponse
{
    /**
     * @var string|null
     */
    private ?string $formation = null;

    /**
     * @var array|null
     */
    private ?array $color = null;

    /**
     * @var PersonResponse[]|null
     */
    private ?array $coaches = null;

    /**
     * @var LineupPlayerResponse[]|null
     */
    private ?array $lineup = null;

    /**
     * @var LineupPlayerResponse[]|null
     */
    private ?array $bench = null;

    /**
     * @var LineupPlayerResponse[]|null
     */
    private ?array $willNotPlay = null;

    /**
     * @param string|null $formation
     * @param array|null $color
     * @param array|null $coaches
     * @param array|null $lineup
     * @param array|null $bench
     * @param array|null $willNotPlay
     * @return LineupDetailResponse
     */
    public static function create(
        ?string $formation = null,
        ?array $color = null,
        ?array $coaches = null,
        ?array $lineup = null,
        ?array $bench = null,
        ?array $willNotPlay = null
    ): LineupDetailResponse
    {
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
