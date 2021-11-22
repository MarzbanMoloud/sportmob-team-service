<?php


namespace App\ValueObjects\Response;


/**
 * Class LineupPlayerResponse
 * @package App\ValueObjects\Response
 */
class LineupPlayerResponse
{
    private PersonResponse $player;
    private int $shirtNumber;
    private string $type;
	private ?int $positionZone = null;
    private ?string $subPosition = null;

    /**
     * @param PersonResponse $player
     * @param int $shirtNumber
     * @param string $type
     * @param int|null $positionZone
     * @param string|null $subPosition
     * @return LineupPlayerResponse
     */
    public static function create(
		PersonResponse $player,
        int $shirtNumber,
		string $type,
		?int $positionZone = null,
		?string $subPosition = null
	): LineupPlayerResponse {
        $instance = new self();
        $instance->player = $player;
        $instance->shirtNumber = $shirtNumber;
        $instance->type = $type;
        $instance->positionZone = $positionZone;
        $instance->subPosition = $subPosition;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'player' => $this->player->toArray(),
            'shirtNumber' => $this->shirtNumber,
            'type' => $this->type,
            'positionZone' => $this->positionZone,
            'subPosition' => $this->subPosition,
        ]);
    }
}
