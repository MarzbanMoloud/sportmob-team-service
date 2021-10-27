<?php


namespace App\ValueObjects\Response;


/**
 * Class TeamFormResponse
 * @package App\ValueObjects\Response
 */
class TeamFormResponse
{
	private TeamResponse $team;
	private ?array $form;

	/**
	 * @param TeamResponse $team
	 * @param array|null $form
	 * @return TeamFormResponse
	 */
	public static function create(
		TeamResponse $team,
		?array $form = null
	): TeamFormResponse {
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
