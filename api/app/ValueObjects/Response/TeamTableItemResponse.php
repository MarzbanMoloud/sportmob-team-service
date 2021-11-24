<?php


namespace App\ValueObjects\Response;


/**
 * Class TeamTableItemResponse
 * @package App\ValueObjects\Response
 */
class TeamTableItemResponse
{
	private ?TeamResponse $team = null;
	private ?int $rank = null;
	private ?int $point = null;
	private ?int $won = null;
	private ?int $drawn = null;
	private ?int $lost = null;
	private ?int $played = null;
	private ?int $goalsDiff = null;
	private ?string $matchStatus = null;

	/**
	 * @param TeamResponse|null $team
	 * @param int|null $rank
	 * @param int|null $point
	 * @param int|null $won
	 * @param int|null $drawn
	 * @param int|null $lost
	 * @param int|null $played
	 * @param int|null $goalsDiff
	 * @param string|null $matchStatus
	 * @return TeamTableItemResponse
	 */
	public static function create(
		?TeamResponse $team = null,
		?int $rank = null,
		?int $point = null,
		?int $won = null,
		?int $drawn = null,
		?int $lost = null,
		?int $played = null,
		?int $goalsDiff = null,
		?string $matchStatus = null
	): TeamTableItemResponse {
		$instance = new self();
		$instance->team = $team;
		$instance->rank = $rank;
		$instance->point = $point;
		$instance->won = $won;
		$instance->drawn = $drawn;
		$instance->lost = $lost;
		$instance->played = $played;
		$instance->goalsDiff = $goalsDiff;
		$instance->matchStatus = $matchStatus;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
            'team' => $this->team ? $this->team->toArray() : null,
            'rank' => $this->rank,
            'point' => $this->point,
            'won' => $this->won,
            'drawn' => $this->drawn,
            'lost' => $this->lost,
            'played' => $this->played,
            'goalsDiff' => $this->goalsDiff,
            'matchStatus' => $this->matchStatus,
        ]);
    }
}
