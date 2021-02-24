<?php


namespace App\Http\Services\Team;


use App\Events\Admin\TeamUpdatedEvent;
use App\Http\Services\Team\Traits\TeamTraits;
use App\ValueObjects\ReadModel\TeamName;
use App\Models\Repositories\TeamRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\ValueObjects\DTO\TeamDTO;
use Aws\DynamoDb\Marshaler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\DynamoDB\DynamoDBException;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TeamService
 * @package App\Http\Services\Team
 */
class TeamService
{
	use TeamTraits;

	private TeamRepository $teamRepository;
	private TeamCacheServiceInterface $teamCacheService;
	private SerializerInterface $serializer;
	private Marshaler $marshaler;

	/**
	 * TeamService constructor.
	 * @param TeamRepository $teamRepository
	 * @param TeamCacheServiceInterface $teamCacheService
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TeamRepository $teamRepository,
		TeamCacheServiceInterface $teamCacheService,
		SerializerInterface $serializer
	) {
		$this->teamRepository = $teamRepository;
		$this->teamCacheService = $teamCacheService;
		$this->marshaler = new Marshaler();
		$this->serializer = $serializer;
	}

	/**
	 * @param string $team
	 * @return \App\Models\ReadModels\Team|mixed
	 */
	public function findTeamById(string $team)
	{
		$result = $this->findTeam($team);
		if (!$result) {
			throw new NotFoundHttpException();
		}
		return $result;
	}

	/**
	 * @param TeamDTO $teamDTO
	 * @throws DynamoDBException
	 */
	public function updateTeam(TeamDTO $teamDTO)
	{
		$teamItem = $this->findTeamById($teamDTO->getId());
		try {
			$teamItem->setName(
				(new TeamName())
					->setOfficial($teamDTO->getOfficialName())
					->setOriginal($teamDTO->getOriginalName())
					->setShort($teamDTO->getShortName())
			);
			$oldTeamModel = $this->marshaler->unmarshalItem(
				$this->teamRepository->persist($teamItem, TeamRepository::RETURN_VALUE_ALL_OLD)['Attributes']
			);
		} catch (\Exception $exception) {
			throw new DynamoDBException(
				'Team Update failed.',
				Response::HTTP_UNPROCESSABLE_ENTITY,
				$exception,
				config('common.error_codes.team_update_failed')
			);
		}
		$normalizeTeamItem = $this->serializer->normalize($teamItem);
		ksort($normalizeTeamItem);
		ksort($oldTeamModel);
		if (md5(json_encode($normalizeTeamItem)) != md5(json_encode($oldTeamModel))) {
			event(new TeamUpdatedEvent($teamItem));
		}
	}
}