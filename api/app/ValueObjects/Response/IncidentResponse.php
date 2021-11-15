<?php


namespace App\ValueObjects\Response;


/**
 * Class IncidentResponse
 * @package App\ValueObjects\Response
 */
class IncidentResponse
{
    private string $id;
    private string $type;
    private ?TeamResponse $team = null;
    private ?int $minute = null;
    private ?int $half = null;
    private ?string $reason = null;
    /**
     * @var PersonResponse[]|null
     */
    private ?array $players = null;
    private ?VarResponse $var = null;

    /**
     * @param string $id
     * @param string $type
     * @param TeamResponse|null $team
     * @param int|null $minute
     * @param int|null $half
     * @param string|null $reason
     * @param array|null $players
     * @param VarResponse|null $var
     * @return IncidentResponse
     */
    public static function create(
		string $id,
		string $type,
		?TeamResponse $team = null,
		?int $minute = null,
		?int $half = null,
		?string $reason = null,
		?array $players = null,
        ?VarResponse $var = null
	): IncidentResponse {
        $instance = new self();
        $instance->id = $id;
        $instance->type = $type;
        $instance->team = $team;
        $instance->minute = $minute;
        $instance->half = $half;
        $instance->reason = $reason;
        $instance->players = $players;
        $instance->var = $var;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'type' => $this->type,
            'team' => $this->team,
            'minute' => $this->minute,
            'half' => $this->half,
            'reason' => $this->reason,
            'players' => $this->players,
            'var' => $this->var->toArray(),
        ]);
    }
}
