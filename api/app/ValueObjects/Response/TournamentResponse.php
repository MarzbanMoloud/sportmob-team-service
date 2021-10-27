<?php


namespace App\ValueObjects\Response;


/**
 * Class TournamentResponse
 * @package App\ValueObjects\Response
 */
class TournamentResponse
{
	private string $id;
	private ?string $season = null;

	/**
	 * @param string $id
	 * @param string $season
	 * @return TournamentResponse
	 */
	public static function create(
		string $id,
		?string $season = null
	): TournamentResponse {
		$instance = new self();
		$instance->id = $id;
		$instance->season = $season;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'season' => $this->season
        ];
    }
}
