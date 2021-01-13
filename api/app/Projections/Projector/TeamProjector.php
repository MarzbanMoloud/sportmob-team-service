<?php


namespace App\Projections\Projector;


use App\Events\Projection\TeamWasCreatedProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Models\ReadModels\Embedded\TeamName;
use App\Models\ReadModels\Team;
use App\Models\Repositories\TeamRepository;
use App\ValueObjects\Broker\Mediator\MessageBody;
use DateTimeImmutable;


/**
 * Class TeamProjector
 * @package App\Projections\Projector
 */
class TeamProjector
{
	/**
	 * @var TeamRepository
	 */
	private TeamRepository $teamRepository;

	/**
	 * TeamProjector constructor.
	 * @param TeamRepository $teamRepository
	 */
	public function __construct(TeamRepository $teamRepository)
	{
		$this->teamRepository = $teamRepository;
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyTeamWasCreated(MessageBody $body): void
	{
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		if (empty($identifier['team'])) {
			throw new ProjectionException('Team field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		$this->checkMetadataValidation($metadata);
		$this->checkItemExist($identifier['team']);
		$teamModel = $this->createTeamModel($identifier['team'], $metadata);
		$this->persistTeam($teamModel);
		event(new TeamWasCreatedProjectorEvent($teamModel));
	}

	/**
	 * @param array $metadata
	 * @throws ProjectionException
	 */
	private function checkMetadataValidation(array $metadata): void
	{
		$requiredFields = [
			'fullName' => 'Full Name',
			'type' => 'Type',
			'country' => 'Country',
			'gender' => 'Gender'
		];
		foreach ($requiredFields as $fieldName => $prettyFieldName) {
			if (empty($metadata[$fieldName])) {
				throw new ProjectionException(
					sprintf("%s field is empty.", $prettyFieldName),
					ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR
				);
			}
		}
		if (is_null($metadata['active'])) {
			throw new ProjectionException(
				sprintf("Active field is empty."),
				ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR
			);
		}
	}

	/**
	 * @param string $teamId
	 * @throws ProjectionException
	 */
	private function checkItemExist(string $teamId): void
	{
		$teamItem = $this->teamRepository->find(['id' => $teamId]);
		if ($teamItem) {
			throw new ProjectionException(
				sprintf("Team already exist by following id: %s", $teamId),
				ResponseServiceInterface::STATUS_CODE_CONFLICT_ERROR
			);
		}
	}

	/**
	 * @param string $teamId
	 * @param array $metadata
	 * @return Team
	 */
	private function createTeamModel(string $teamId, array $metadata): Team
	{
		return (new Team())
			->setId($teamId)
			->setGender( $metadata[ 'gender' ])
			->setFounded( $metadata[ 'founded' ] ?? '' )
			->setCountry( $metadata[ 'country' ] )
			->setCity( $metadata[ 'city' ] ?? '' )
			->setType( $metadata[ 'type' ] )
			->setCreatedAt(new DateTimeImmutable())
			->setName(
				(new TeamName())
					->setOriginal( $metadata[ 'fullName' ] )
					->setOfficial( $metadata[ 'officialName' ] )
					->setShort( $metadata[ 'shortName' ] )
			);
	}

	/**
	 * @param Team $teamModel
	 * @throws ProjectionException
	 */
	private function persistTeam(Team $teamModel): void
	{
		try {
			$this->teamRepository->persist($teamModel);
		} catch (DynamoDBRepositoryException $exception) {
			throw new ProjectionException('Failed to persist team.', $exception->getCode());
		}
	}
}