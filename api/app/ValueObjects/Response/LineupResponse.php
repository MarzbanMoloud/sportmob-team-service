<?php


namespace App\ValueObjects\Response;


/**
 * Class LineupResponse
 * @package App\ValueObjects\Response
 */
class LineupResponse
{
    private MatchResponse $match;
    private LineupDetailResponse $homeTeamLineup;
    private LineupDetailResponse $awayTeamLineup;

	/**
	 * @param MatchResponse $match
	 * @param LineupDetailResponse $homeTeamLineup
	 * @param LineupDetailResponse $awayTeamLineup
	 * @return LineupResponse
	 */
    public static function create(
		MatchResponse $match,
		LineupDetailResponse $homeTeamLineup,
		LineupDetailResponse $awayTeamLineup
	): LineupResponse {
        $instance = new self();
        $instance->match = $match;
        $instance->homeTeamLineup = $homeTeamLineup;
        $instance->awayTeamLineup = $awayTeamLineup;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'match' => $this->match->toArray(),
            'homeTeamLineup' => $this->homeTeamLineup->toArray(),
            'awayTeamLineup' => $this->awayTeamLineup->toArray(),
        ]);
    }
}
