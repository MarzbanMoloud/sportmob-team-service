<?php


namespace App\ValueObjects\Response;


/**
 * Class CalendarResponse
 * @package App\ValueObjects\Response
 */
class CalendarResponse
{
    /**
     * @var MatchCalendarResponse[]
     */
    private array $matches;

    /**
     * @param array $matches
     * @return CalendarResponse
     */
    public static function create(
		array $matches
	): CalendarResponse {
        $instance = new self();
        $instance->matches = $matches;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'matches' => $this->matches,
        ]);
    }
}
