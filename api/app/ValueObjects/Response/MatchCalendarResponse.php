<?php


namespace App\ValueObjects\Response;


/**
 * Class MatchCalendarResponse
 * @package App\ValueObjects\Response
 */
class MatchCalendarResponse
{
    private MatchResponse $match;
    private ?int $priority = null;

    /**
     * @param MatchResponse $match
     * @param int|null $priority
     * @return MatchCalendarResponse
     */
    public static function create(
        MatchResponse $match,
        ?int $priority
    ): MatchCalendarResponse
    {
        $instance = new self;
        $instance->match = $match;
        $instance->priority = $priority;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_filter([
            'match' => $this->match->toArray(),
            'priority' => $this->priority,
        ]);
    }

}