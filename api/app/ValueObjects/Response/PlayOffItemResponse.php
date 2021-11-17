<?php


namespace App\ValueObjects\Response;


/**
 * Class PlayOffItemResponse
 * @package App\ValueObjects\Response
 */
class PlayOffItemResponse
{
	private ?StageResponse $stage = null;

	/**
	 * @var $matches MatchResponse[]
	 */
	private ?array $matches = null;

	/**
	 * @param StageResponse|null $stage
	 * @param array|null $matches
	 * @return PlayOffItemResponse
	 */
	public static function create(
		?StageResponse $stage = null,
		?array $matches = null
	): PlayOffItemResponse {
		$instance = new self();
		$instance->stage = $stage;
		$instance->matches = $matches;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
            'stage' => $this->stage ? $this->stage->toArray() : null,
            'matches' => $this->matches,
        ]);
    }
}
