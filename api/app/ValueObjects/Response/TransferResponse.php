<?php


namespace App\ValueObjects\Response;


/**
 * Class TransferResponse
 * @package App\ValueObjects\Response
 */
class TransferResponse
{
	private string $id;
	private TeamResponse $toTeam;
	private string $type;
	private int $like;
	private int $dislike;
	private ?PersonResponse $person = null;
	private ?TeamResponse $fromTeam = null;
	private ?string $season = null;
	private ?int $startDate = null;
	private ?int $endDate = null;
	private ?int $marketValue = null;

	/**
	 * @param string $id
	 * @param TeamResponse $toTeam
	 * @param string $type
	 * @param int $like
	 * @param int $dislike
	 * @param PersonResponse|null $person
	 * @param TeamResponse|null $fromTeam
	 * @param string $season
	 * @param int|null $startDate
	 * @param int|null $endDate
	 * @param int|null $marketValue
	 * @return TransferResponse
	 */
	public static function create(
		string $id,
		TeamResponse $toTeam,
		string $type,
		int $like,
		int $dislike,
		?PersonResponse $person = null,
		?TeamResponse $fromTeam = null,
		?string $season = null,
		?int $startDate = null,
		?int $endDate = null,
		?int $marketValue = null
	): TransferResponse {
		$instance = new self();
		$instance->id = $id;
		$instance->toTeam = $toTeam;
		$instance->type = $type;
		$instance->like = $like;
		$instance->dislike = $dislike;
		$instance->person = $person;
		$instance->fromTeam = $fromTeam;
		$instance->season = $season;
		$instance->startDate = $startDate;
		$instance->endDate = $endDate;
		$instance->marketValue = $marketValue;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'toTeam' => $this->toTeam->toArray(),
            'fromTeam' => $this->fromTeam ? $this->fromTeam->toArray() : null,
			'person' => $this->person ? $this->person->toArray() : null,
			'marketValue' => $this->marketValue,
			'startDate' => $this->startDate,
			'endDate' => $this->endDate,
			'type' => $this->type,
			'like' => $this->like,
			'dislike' => $this->dislike,
			'season' => $this->season,
        ]);
    }
}
