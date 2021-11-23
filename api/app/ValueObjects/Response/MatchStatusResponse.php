<?php


namespace App\ValueObjects\Response;


/**
 * Class MatchStatusResponse
 * @package App\ValueObjects\Response
 */
class MatchStatusResponse
{
    private string $notStarted;
    private ?string $gamedEnded = null;
    private ?string $gameStarted = null;
    private ?string $firstHalfEnded = null;
    private ?string $secondHalfStarted = null;
    private ?string $secondHalfEnded = null;
    private ?string $extraTimeStarted = null;
    private ?string $extraTimeFirstHalfEnded = null;
    private ?string $extraTimeSecondHalfStarted = null;
    private ?string $extraTimeSecondHalfEnded = null;
    private ?string $penalty = null;
    private ?string $cancelled = null;
    private ?string $postponed = null;
    private ?string $suspended = null;

    /**
     * @param string $notStarted
     * @param string|null $gamedEnded
     * @param string|null $gameStarted
     * @param string|null $firstHalfEnded
     * @param string|null $secondHalfStarted
     * @param string|null $secondHalfEnded
     * @param string|null $extraTimeStarted
     * @param string|null $extraTimeFirstHalfEnded
     * @param string|null $extraTimeSecondHalfStarted
     * @param string|null $extraTimeSecondHalfEnded
     * @param string|null $penalty
     * @param string|null $cancelled
     * @param string|null $postponed
     * @param string|null $suspended
     * @return MatchStatusResponse
     */
    public static function create(
        string $notStarted,
        ?string $gamedEnded = null,
        ?string $gameStarted = null,
        ?string $firstHalfEnded = null,
        ?string $secondHalfStarted = null,
        ?string $secondHalfEnded = null,
        ?string $extraTimeStarted = null,
        ?string $extraTimeFirstHalfEnded = null,
        ?string $extraTimeSecondHalfStarted = null,
        ?string $extraTimeSecondHalfEnded = null,
        ?string $penalty = null,
        ?string $cancelled = null,
        ?string $postponed = null,
        ?string $suspended = null
    ): MatchStatusResponse
    {
        $instance = new self();
        $instance->notStarted = $notStarted;
        $instance->gamedEnded = $gamedEnded;
        $instance->gameStarted = $gameStarted;
        $instance->firstHalfEnded = $firstHalfEnded;
        $instance->secondHalfStarted = $secondHalfStarted;
        $instance->secondHalfEnded = $secondHalfEnded;
        $instance->extraTimeStarted = $extraTimeStarted;
        $instance->extraTimeFirstHalfEnded = $extraTimeFirstHalfEnded;
        $instance->extraTimeSecondHalfStarted = $extraTimeSecondHalfStarted;
        $instance->extraTimeSecondHalfEnded = $extraTimeSecondHalfEnded;
        $instance->penalty = $penalty;
        $instance->cancelled = $cancelled;
        $instance->postponed = $postponed;
        $instance->suspended = $suspended;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'notStarted' => $this->notStarted,
            'gameStarted' => $this->gameStarted,
            'firstHalfEnded' => $this->firstHalfEnded,
            'secondHalfStarted' => $this->secondHalfStarted,
            'secondHalfEnded' => $this->secondHalfEnded,
            'extraTimeStarted' => $this->extraTimeStarted,
            'extraTimeFirstHalfEnded' => $this->extraTimeFirstHalfEnded,
            'extraTimeSecondHalfStarted' => $this->extraTimeSecondHalfStarted,
            'extraTimeSecondHalfEnded' => $this->extraTimeSecondHalfEnded,
            'penalty' => $this->penalty,
            'cancelled' => $this->cancelled,
            'postponed' => $this->postponed,
            'suspended' => $this->suspended,
            'gamedEnded' => $this->gamedEnded,
        ]);
    }
}
