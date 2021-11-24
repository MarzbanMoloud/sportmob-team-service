<?php


namespace App\ValueObjects\Response;


/**
 * Class TimeStatusResponse
 * @package App\ValueObjects\Response
 */
class TimeStatusResponse
{
	private ?string $gameStarted = null;
	private ?string $gamedEnded = null;
    private ?string $firstHalfEnded = null;
    private ?string $secondHalfStarted = null;
    private ?string $secondHalfEnded = null;
    private ?string $extraTimeStarted = null;
    private ?string $extraTimeFirstHalfEnded = null;
    private ?string $extraTimeSecondHalfStarted = null;
    private ?string $extraTimeSecondHalfEnded = null;

	/**
	 * @param string|null $gameStarted
	 * @param string|null $gamedEnded
	 * @param string|null $firstHalfEnded
	 * @param string|null $secondHalfStarted
	 * @param string|null $secondHalfEnded
	 * @param string|null $extraTimeStarted
	 * @param string|null $extraTimeFirstHalfEnded
	 * @param string|null $extraTimeSecondHalfStarted
	 * @param string|null $extraTimeSecondHalfEnded
	 * @return TimeStatusResponse
	 */
    public static function create(
		?string $gameStarted = null,
		?string $gamedEnded = null,
        ?string $firstHalfEnded = null,
        ?string $secondHalfStarted = null,
        ?string $secondHalfEnded = null,
        ?string $extraTimeStarted = null,
        ?string $extraTimeFirstHalfEnded = null,
        ?string $extraTimeSecondHalfStarted = null,
        ?string $extraTimeSecondHalfEnded = null
    ): TimeStatusResponse
    {
        $instance = new self();
        $instance->gamedEnded = $gamedEnded;
        $instance->gameStarted = $gameStarted;
        $instance->firstHalfEnded = $firstHalfEnded;
        $instance->secondHalfStarted = $secondHalfStarted;
        $instance->secondHalfEnded = $secondHalfEnded;
        $instance->extraTimeStarted = $extraTimeStarted;
        $instance->extraTimeFirstHalfEnded = $extraTimeFirstHalfEnded;
        $instance->extraTimeSecondHalfStarted = $extraTimeSecondHalfStarted;
        $instance->extraTimeSecondHalfEnded = $extraTimeSecondHalfEnded;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'gameStarted' => $this->gameStarted,
            'firstHalfEnded' => $this->firstHalfEnded,
            'secondHalfStarted' => $this->secondHalfStarted,
            'secondHalfEnded' => $this->secondHalfEnded,
            'extraTimeStarted' => $this->extraTimeStarted,
            'extraTimeFirstHalfEnded' => $this->extraTimeFirstHalfEnded,
            'extraTimeSecondHalfStarted' => $this->extraTimeSecondHalfStarted,
            'extraTimeSecondHalfEnded' => $this->extraTimeSecondHalfEnded,
            'gamedEnded' => $this->gamedEnded,
        ]);
    }
}
