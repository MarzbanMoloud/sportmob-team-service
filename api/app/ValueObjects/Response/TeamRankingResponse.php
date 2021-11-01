<?php


namespace App\ValueObjects\Response;


/**
 * Class TeamRankingResponse
 * @package App\ValueObjects\Response
 */
class TeamRankingResponse
{
    private TeamResponse $team;
    private ?int $rank = null;
    private ?int $point = null;
    private ?int $played = null;
    private ?int $won = null;
    private ?int $lost = null;
    private ?int $drawn = null;
    private ?int $goalsFor = null;
    private ?int $goalsAgainst = null;

	/**
	 * @param TeamResponse $team
	 * @param int|null $rank
	 * @param int|null $point
	 * @param int|null $played
	 * @param int|null $won
	 * @param int|null $lost
	 * @param int|null $drawn
	 * @param int|null $goalsFor
	 * @param int|null $goalsAgainst
	 * @return TeamRankingResponse
	 */
    public static function create(
		TeamResponse $team,
		?int $rank = null,
		?int $point = null,
		?int $played = null,
		?int $won = null,
		?int $lost = null,
		?int $drawn = null,
		?int $goalsFor = null,
		?int $goalsAgainst = null
	): TeamRankingResponse {
        $instance = new self();
        $instance->team = $team;
        $instance->rank = $rank;
        $instance->point = $point;
        $instance->played = $played;
        $instance->won = $won;
        $instance->lost = $lost;
        $instance->drawn = $drawn;
        $instance->goalsFor = $goalsFor;
        $instance->goalsAgainst = $goalsAgainst;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'team' => $this->team,
            'rank' => $this->rank,
            'point' => $this->point,
            'played' => $this->played,
            'won' => $this->won,
            'lost' => $this->lost,
            'drawn' => $this->drawn,
            'goalsFor' => $this->goalsFor,
            'goalsAgainst' => $this->goalsAgainst,
        ]);
    }
}
