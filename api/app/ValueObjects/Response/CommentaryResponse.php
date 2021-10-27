<?php


namespace App\ValueObjects\Response;


/**
 * Class CommentaryResponse
 * @package App\ValueObjects\Response
 */
class CommentaryResponse
{
    private string $id;
    private MatchResponse $match;
	private string $type;
	private string $comment;
	private string $minute;
	private array $players;
	private ?string $half = null;

	/**
	 * @param string $id
	 * @param MatchResponse $match
	 * @param string $type
	 * @param string $comment
	 * @param string $minute
	 * @param array $players
	 * @param string|null $half
	 * @return CommentaryResponse
	 */
    public static function create(
    	string $id,
		MatchResponse $match,
		string $type,
		string $comment,
		string $minute,
		array $players,
		?string $half = null
	): CommentaryResponse {
        $instance = new self();
        $instance->id = $id;
        $instance->match = $match;
        $instance->type = $type;
        $instance->comment = $comment;
        $instance->minute = $minute;
        $instance->players = $players;
        $instance->half = $half;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'match' => $this->match->toArray(),
			'type' => $this->type,
			'comment' => $this->comment,
			'minute' => $this->minute,
			'players' => $this->players,
			'half' => $this->half,
        ];
    }
}
