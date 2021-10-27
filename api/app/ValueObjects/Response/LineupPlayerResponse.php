<?php


namespace App\ValueObjects\Response;


/**
 * Class LineupPlayerResponse
 * @package App\ValueObjects\Response
 */
class LineupPlayerResponse
{
    private PersonResponse $player;
    private ?int $shirtNumber = null;
    private string $type;
	private int $positionZone;
    private ?string $subPosition = null;

	/**
	 * @param PersonResponse $player
	 * @param string $type
	 * @param int $positionZone
	 * @param int|null $shirtNumber
	 * @param string|null $subPosition
	 * @return LineupPlayerResponse
	 */
    public static function create(
		PersonResponse $player,
		string $type,
		int $positionZone,
		?int $shirtNumber = null,
		?string $subPosition = null
	): LineupPlayerResponse {
        $instance = new self();
        $instance->player = $player;
        $instance->type = $type;
        $instance->positionZone = $positionZone;
        $instance->shirtNumber = $shirtNumber;
        $instance->subPosition = $subPosition;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'player' => $this->player->toArray(),
            'type' => $this->type,
            'positionZone' => $this->positionZone,
            'shirtNumber' => $this->shirtNumber,
            'subPosition' => $this->subPosition,
        ];
    }
}
