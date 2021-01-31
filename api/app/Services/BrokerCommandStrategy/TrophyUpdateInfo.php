<?php


namespace App\Services\BrokerCommandStrategy;


use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Listeners\Projection\TrophyProjectorListener;
use App\Models\ReadModels\Trophy;
use App\Models\Repositories\TrophyRepository;
use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandEventInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
use Sentry\State\HubInterface;


/**
 * Class TrophyUpdateInfo
 * @package App\Services\BrokerCommandStrategy
 */
class TrophyUpdateInfo implements BrokerCommandEventInterface
{
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private HubInterface $sentryHub;
	private TrophyRepository $trophyRepository;

	/**
	 * TrophyUpdateInfo constructor.
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param HubInterface $sentryHub
	 * @param TrophyRepository $trophyRepository
	 */
	public function __construct(
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		HubInterface $sentryHub,
		TrophyRepository $trophyRepository
	) {
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->sentryHub = $sentryHub;
		$this->trophyRepository = $trophyRepository;
	}

	/**
	 * @param Headers $headers
	 * @return bool
	 */
	public function support(Headers $headers): bool
	{
		return
			($headers->getDestination() == config('broker.services.team_name')) &&
			($headers->getKey() == TrophyProjectorListener::BROKER_EVENT_KEY);
	}

	/**
	 * @param Message $commandQuery
	 */
	public function handle(Message $commandQuery): void
	{
		if (empty($commandQuery->getBody())) {
			return;
		}
		$tournamentId = $commandQuery->getHeaders()->getId();
		$trophies = $this->trophyRepository->findByTournamentId($tournamentId);
		foreach ($trophies as $trophy) {
			/**
			 * @var Trophy $trophy
			 */
			$trophy
				->setTournamentSeason($commandQuery->getBody()['season'])
				->setCompetitionName($commandQuery->getBody()['competitionName']);
			try {
				$this->trophyRepository->persist($trophy);
			} catch (DynamoDBRepositoryException $exception) {
				$this->sentryHub->captureException($exception);
			}
		}
		$data = $commandQuery->getBody();
		unset($data['entity']);
		$this->brokerMessageCacheService->putTournamentInfo($data);
	}
}