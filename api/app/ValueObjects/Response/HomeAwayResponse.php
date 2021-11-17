<?php


namespace App\ValueObjects\Response;


/**
 * Class HomeAwayResponse
 * @package App\ValueObjects\Response
 */
class HomeAwayResponse
{
	private ?int $home = null;
	private ?int $away = null;

	/**
	 * @param int|null $home
	 * @param int|null $away
	 * @return HomeAwayResponse
	 */
	public static function create(
		?int $home = null,
		?int $away = null
	): HomeAwayResponse {
		$instance = new self();
		$instance->home = $home;
		$instance->away = $away;
		return $instance;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return array_filter([
			'home' => $this->home,
			'away' => $this->away
		]);
	}
}
