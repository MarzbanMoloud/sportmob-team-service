<?php


namespace App\ValueObjects\Response;


/**
 * Class MatchCalendarResponse
 * @package App\ValueObjects\Response
 */
class MatchCalendarResponse
{
    private MatchResponse $match;
    private ?int $competitionPriority = null;
    private ?int $matchPriority = null;

    /**
     * @param MatchResponse $match
     * @param int|null $competitionPriority
     * @param int|null $matchPriority
     * @return MatchCalendarResponse
     */
    public static function create(
        MatchResponse $match,
        ?int $competitionPriority,
        ?int $matchPriority
    ): MatchCalendarResponse
    {
        $instance = new self;
        $instance->match = $match;
        $instance->competitionPriority = $competitionPriority;
        $instance->matchPriority = $matchPriority;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_filter([
            'match' => $this->match->toArray(),
            'competitionPriority' => $this->competitionPriority,
            'matchPriority' => $this->matchPriority,
        ]);
    }

}