<?php


namespace App\Projections\Projector;


use App\Events\Projection\TeamWasCreatedProjectorEvent;
use App\Events\Projection\TeamWasUpdatedProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Models\ReadModels\Team;
use App\Models\Repositories\TeamRepository;
use App\ValueObjects\Broker\Mediator\MessageBody;
use App\ValueObjects\ReadModel\TeamName;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TeamProjector
 * @package App\Projections\Projector
 */
class TeamProjector
{
	private TeamRepository $teamRepository;
	private LoggerInterface $logger;
	private SerializerInterface $serializer;
	private string $eventName;

	/**
	 * TeamProjector constructor.
	 * @param TeamRepository $teamRepository
	 * @param LoggerInterface $logger
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TeamRepository $teamRepository,
		LoggerInterface $logger,
		SerializerInterface $serializer
	) {
		$this->teamRepository = $teamRepository;
		$this->logger = $logger;
		$this->serializer = $serializer;
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyTeamWasCreated(MessageBody $body): void
	{
		$this->eventName = config('mediator-event.events.team_was_created');
		$this->logger->alert(
			sprintf("%s handler in progress.", $this->eventName),
			$this->serializer->normalize($body, 'array')
		);
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		if (empty($identifier['team'])) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Team field is empty.'
				), $identifier
			);
			throw new ProjectionException('Team field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		$this->checkMetadataValidation($metadata);
		$this->checkItemExist($identifier['team']);
		$teamModel = $this->createTeamModel($identifier['team'], $metadata);
		$this->persistTeam($teamModel);
		event(new TeamWasCreatedProjectorEvent($teamModel));
		$this->logger->alert(
			sprintf("%s handler completed successfully.", $this->eventName),
			$this->serializer->normalize($teamModel, 'array')
		);
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyTeamWasUpdated(MessageBody $body): void
	{
		$this->eventName = config('mediator-event.events.team_was_updated');
		$this->logger->alert(
			sprintf("%s handler in progress.", $this->eventName),
			$this->serializer->normalize($body, 'array')
		);
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		if (empty($identifier['team'])) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Team field is empty.'
				), $identifier
			);
			throw new ProjectionException('Team field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		if (empty($metadata['fullName'])) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'FullName field is empty.'
				), $metadata
			);
			throw new ProjectionException('FullName field is empty.', ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
		$teamModel = $this->checkItemNotExist($identifier['team'], $this->serializer->normalize($body, 'array'));
		$teamModel = $this->updateTeamModel($teamModel, $metadata);
		$this->persistTeam($teamModel);
		event(new TeamWasUpdatedProjectorEvent($teamModel));
		$this->logger->alert(
			sprintf("%s handler completed successfully.", $this->eventName),
			$this->serializer->normalize($teamModel, 'array')
		);
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
				$this->logger->alert(
					sprintf(
						"%s handler failed because of %s",
						$this->eventName,
						sprintf("%s field is empty.", $prettyFieldName)
					), $metadata
				);
				throw new ProjectionException(
					sprintf("%s field is empty.", $prettyFieldName),
					ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR
				);
			}
		}
		if (is_null($metadata['active'])) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Active field is empty.'
				), $metadata
			);
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
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					sprintf("Team already exist by following id: %s", $teamId)
				), $this->serializer->normalize($teamItem, 'array')
			);
			throw new ProjectionException(
				sprintf("Team already exist by following id: %s", $teamId),
				ResponseServiceInterface::STATUS_CODE_CONFLICT_ERROR
			);
		}
	}

	/**
	 * @param string $teamId
	 * @param array $body
	 * @return \App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryModelInterface
	 * @throws ProjectionException
	 */
	private function checkItemNotExist(string $teamId, array $body)
	{
		$teamItem = $this->teamRepository->find(['id' => $teamId]);
		if (!$teamItem) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					sprintf("Team already not exist by following id: %s", $teamId)
				), $body
			);
			throw new ProjectionException(
				sprintf("Team already not exist by following id: %s", $teamId),
				ResponseServiceInterface::STATUS_CODE_CONFLICT_ERROR
			);
		}
		return $teamItem;
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
			->setGender($metadata['gender'])
			->setFounded($metadata['founded'] ?? '' )
			->setCountry($metadata['country'])
			->setCountryId($metadata['countryId'])
			->setCity($metadata['city'] ?? '')
			->setType($metadata['type'])
			->setName(
				(new TeamName())
					->setOriginal($metadata['fullName'])
					->setOfficial($metadata['officialName'])
					->setShort($metadata['shortName'])
			);
	}

	/**
	 * @param \App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryModelInterface $teamModel
	 * @param array $metadata
	 * @return mixed
	 */
	private function updateTeamModel(
		\App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryModelInterface $teamModel,
		array $metadata
	) {
		return $teamModel->setName(
			(new TeamName())
				->setOriginal($metadata['fullName'])
				->setOfficial($metadata['officialName'])
				->setShort($metadata['shortName'])
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
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Failed to persist team.'
				), $this->serializer->normalize($teamModel, 'array')
			);
			throw new ProjectionException('Failed to persist team.', $exception->getCode(), $exception);
		}
	}
}