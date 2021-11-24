<?php


namespace App\ValueObjects\Response;


/**
 * Class TeamOverviewResponse
 * @package App\ValueObjects\Response
 */
class TeamOverviewResponse
{
    private ?MatchResponse $nextMatch = null;
    private ?TeamFormResponse $teamForm = null;

	/**
	 * @param MatchResponse|null $nextMatch
	 * @param TeamFormResponse|null $teamForm
	 * @return TeamOverviewResponse
	 */
    public static function create(
		?MatchResponse $nextMatch = null,
		?TeamFormResponse $teamForm = null
	): TeamOverviewResponse {
        $instance = new self();
        $instance->nextMatch = $nextMatch;
        $instance->teamForm = $teamForm;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
			'nextMatch' => $this->nextMatch ? $this->nextMatch->toArray() : null,
			'teamForm' => $this->teamForm ? $this->teamForm->toArray() : null,
		]);
    }
}
