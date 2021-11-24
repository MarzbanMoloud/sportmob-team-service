<?php


namespace App\ValueObjects\Response;


/**
 * Class TeamFormSymbols
 * @package App\ValueObjects\Response
 */
class TeamFormSymbols
{
	private TeamResponse $team;
	private ?array $form = null;

	/**
	 * @param TeamResponse $team
	 * @param array|null $form
	 * @return TeamFormSymbols
	 */
	public static function create(
		TeamResponse $team,
		?array $form
	): TeamFormSymbols {
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
