<?php


namespace App\ValueObjects\Response;


/**
 * Class CommentaryPlayerResponse
 * @package App\ValueObjects\Response
 */
class CommentaryPlayerResponse
{
    private PersonResponse $player;
    private ?TeamResponse $team = null;

	/**
	 * @param PersonResponse $player
	 * @param TeamResponse|null $team
	 * @return CommentaryPlayerResponse
	 */
    public static function create(
		PersonResponse $player,
		?TeamResponse $team = null
	): CommentaryPlayerResponse {
        $instance = new self();
        $instance->player = $player;
        $instance->team = $team;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'player' => $this->player->toArray(),
            'team' => $this->team ? $this->team->toArray() : null,
        ]);
    }
}
