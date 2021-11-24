<?php


namespace App\ValueObjects\Response;


/**
 * Class TeamFormSymbolsResponse
 * @package App\ValueObjects\Response
 */
class TeamFormSymbolsResponse
{
	private TeamResponse $team;
	private ?array $form = null;

	/**
	 * @param TeamResponse $team
	 * @param array|null $form
	 * @return TeamFormSymbolsResponse
	 */
	public static function create(
		TeamResponse $team,
		?array $form = null
	): TeamFormSymbolsResponse {
		$instance = new self();
		$instance->team = $team;
		$instance->form = $form;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
            'team' => $this->team->toArray(),
            'form' => $this->form,
        ]);
    }
}
