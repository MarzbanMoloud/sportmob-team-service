<?php


namespace App\ValueObjects\Response;


/**
 * Class MatchStatResponse
 * @package App\ValueObjects\Response
 */
class MatchStatResponse
{
    private string $home;
    private string $away;
    private string $type;

	/**
	 * @param string $home
	 * @param string $away
	 * @param string $type
	 * @return MatchStatResponse
	 */
    public static function create(
		string $home,
		string $away,
		string $type
	): MatchStatResponse {
        $instance = new self();
        $instance->home = $home;
        $instance->away = $away;
        $instance->type = $type;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'home' => $this->home,
            'away' => $this->away,
            'type' => $this->type,
        ];
    }
}
