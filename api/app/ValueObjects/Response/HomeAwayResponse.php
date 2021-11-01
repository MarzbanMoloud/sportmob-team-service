<?php


namespace App\ValueObjects\Response;


/**
 * Class HomeAwayResponse
 * @package App\ValueObjects\Response
 */
class HomeAwayResponse
{
	private int $home;
	private int $away;

	/**
	 * @param int $home
	 * @param int $away
	 * @return HomeAwayResponse
	 */
	public static function create(
		int $home,
		int $away
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
