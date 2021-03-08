<?php


namespace App\Services\BrokerQueryStrategy;


use App\Models\Repositories\TeamRepository;
use App\Services\BrokerInterface;
use App\Services\BrokerQueryStrategy\Interfaces\BrokerQueryEventInterface;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Carbon\Carbon;


/**
 * Class TeamInformation
 * @package App\Services\BrokerQueryStrategy
 */
class TeamInformation implements BrokerQueryEventInterface
{
	private SerializerInterface $serializer;
	private BrokerInterface $broker;
	private TeamRepository $teamRepository;
	private LoggerInterface $logger;

	/**
	 * TeamInformation constructor.
	 * @param BrokerInterface $broker
	 * @param TeamRepository $teamRepository
	 * @param SerializerInterface $serializer
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		BrokerInterface $broker,
		TeamRepository $teamRepository,
		SerializerInterface $serializer,
		LoggerInterface $logger
	) {
		$this->broker = $broker;
		$this->serializer = $serializer;
		$this->teamRepository = $teamRepository;
		$this->logger = $logger;
	}

	/**
	 * @param Message $commandQuery
	 * @return bool
	 */
	public function support(Message $commandQuery): bool
	{
		return
			($commandQuery->getHeaders()->getDestination() == config('broker.services.team_name')) &&
			($commandQuery->getBody()['entity'] == config('broker.services.team_name'));
	}

	/**
	 * @param Message $commandQuery
	 */
	public function handle(Message $commandQuery): void
	{
		$this->logger->alert(
			sprintf(
				"Question %s by %s will handle by %s.",
				$commandQuery->getHeaders()->getKey(),
				$commandQuery->getHeaders()->getSource(),
				__CLASS__
			),
			$this->serializer->normalize($commandQuery, 'array')
		);
		$this->logger->alert(
			sprintf("%s handler in progress.", $commandQuery->getHeaders()->getKey()),
			$this->serializer->normalize($commandQuery, 'array')
		);

		$teamItem = $this->teamRepository->find(['id' => $commandQuery->getBody()['id']]);

		$teamItemArray = [];
		if($teamItem) {
			$teamItemArray = $this->serializer->normalize($teamItem);
			$teamItemArray['entity'] = $commandQuery->getBody()['entity'];
		}

		$message = (new Message())
			->setHeaders(
				(new Headers())
					->setKey($commandQuery->getHeaders()->getKey())
					->setId($commandQuery->getHeaders()->getId())
					->setDestination($commandQuery->getHeaders()->getSource())
					->setSource($commandQuery->getHeaders()->getDestination())
					->setDate(Carbon::now()->format('c'))
			)->setBody($teamItemArray);
		$this->broker->flushMessages()->addMessage(
			$commandQuery->getHeaders()->getKey(),
			$this->serializer->serialize($message, 'json')
		)->produceMessage(config('broker.topics.answer'));

		$this->logger->alert(
			sprintf("%s handler completed successfully.", $commandQuery->getHeaders()->getKey()),
			$this->serializer->normalize($message, 'array')
		);
	}
}