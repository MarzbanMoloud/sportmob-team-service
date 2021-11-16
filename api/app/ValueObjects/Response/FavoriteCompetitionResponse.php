<?php


namespace App\ValueObjects\Response;


/**
 * Class FavoriteCompetitionResponse
 * @package App\ValueObjects\Response
 */
class FavoriteCompetitionResponse
{
	private CompetitionResponse $competition;
	private ?TopStatTeamResponse $topTeam = null;
	private ?TopStatPersonResponse $topScorer = null;
	private ?TopStatPersonResponse $topAssists = null;

	/**
	 * @param CompetitionResponse $competition
	 * @param TopStatTeamResponse|null $topTeam
	 * @param TopStatPersonResponse|null $topScorer
	 * @param TopStatPersonResponse|null $topAssists
	 * @return FavoriteCompetitionResponse
	 */
	public static function create(
		CompetitionResponse $competition,
		?TopStatTeamResponse $topTeam = null,
		?TopStatPersonResponse $topScorer = null,
		?TopStatPersonResponse $topAssists = null
	): FavoriteCompetitionResponse {
		$instance = new self();
		$instance->competition = $competition;
		$instance->topTeam = $topTeam;
		$instance->topScorer = $topScorer;
		$instance->topAssists = $topAssists;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
    		'competition' => $this->competition->toArray(),
    		'topTeam' => $this->topTeam ? $this->topTeam->toArray() : null,
    		'topScorer' => $this->topScorer ? $this->topScorer->toArray() : null,
    		'topAssists' => $this->topAssists ? $this->topAssists->toArray() : null,
        ]);
    }
}
