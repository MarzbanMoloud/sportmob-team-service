<?php


namespace App\ValueObjects\Response;


/**
 * Class MatchStatisticResponse
 * @package App\ValueObjects\Response
 */
class MatchStatisticResponse
{
    private MatchResponse $match;
    private array $stats;

	/**
	 * @param MatchResponse $match
	 * @param array $stats
	 * @return MatchStatisticResponse
	 */
    public static function create(
		MatchResponse $match,
		array $stats
	): MatchStatisticResponse {
        $instance = new self();
        $instance->match = $match;
        $instance->stats = $stats;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'match' => $this->match,
            'stats' => $this->stats
        ];
    }
}
