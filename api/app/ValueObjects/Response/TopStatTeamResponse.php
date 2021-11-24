<?php


namespace App\ValueObjects\Response;


/**
 * Class TopStatTeamResponse
 * @package App\ValueObjects\Response
 */
class TopStatTeamResponse
{
	private TeamResponse $team;
	private ?StatItemResponse $stat1 = null;
	private ?StatItemResponse $stat2 = null;

	/**
	 * @param TeamResponse $team
	 * @param StatItemResponse|null $stat1
	 * @param StatItemResponse|null $stat2
	 * @return TopStatTeamResponse
	 */
    public static function create(
		TeamResponse $team,
		?StatItemResponse $stat1 = null,
		?StatItemResponse $stat2 = null
    ): TopStatTeamResponse {
        $instance = new self();
        $instance->team = $team;
        $instance->stat1 = $stat1;
        $instance->stat2 = $stat2;
        return $instance;
    }

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return array_filter([
			'team' => $this->team->toArray(),
			'stat1' => $this->stat1 ? $this->stat1->toArray() : null,
			'stat2' => $this->stat2 ? $this->stat2->toArray() : null,
		]);
	}
}
