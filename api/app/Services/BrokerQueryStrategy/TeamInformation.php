<?php


namespace App\Services\BrokerQueryStrategy;


use App\Models\Repositories\TeamRepository;
use App\Services\BrokerInterface;
use App\Services\BrokerQueryStrategy\Interfaces\BrokerQueryEventInterface;
use App\Services\Logger\Question;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
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

	/**
	 * TeamInformation constructor.
	 * @param BrokerInterface $broker
	 * @param TeamRepository $teamRepository
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		BrokerInterface $broker,
		TeamRepository $teamRepository,
		SerializerInterface $serializer
	) {
		$this->broker = $broker;
		$this->serializer = $serializer;
		$this->teamRepository = $teamRepository;
	}

	/**
	 * @param Message $commandQuery
	 */
	public function handle(Message $commandQuery): void
	{
		Question::handled($commandQuery, $commandQuery->getHeaders()->getKey(), $commandQuery->getHeaders()->getSource(), __CLASS__);
		Question::processing($commandQuery, $commandQuery->getHeaders()->getKey());

		$teamItem = $this->teamRepository->find(['id' => $commandQuery->getBody()['id']]);

		$teamItemArray = [];
		if($teamItem) {
			$teamItemArray = $this->serializer->normalize($teamItem);
			$teamItemArray['entity'] = $commandQuery->getBody()['entity'];
		}

		$message = (new Message())
			->setHeaders(
				(new Headers())
					->setEventId($commandQuery->getHeaders()->getEventId())
					->setKey($commandQuery->getHeaders()->getKey())
					->setId($commandQuery->getHeaders()->getId())
					->setDestination($commandQuery->getHeaders()->getSource())
					->setSource($commandQuery->getHeaders()->getDestination())
					->setDate(Carbon::now()->format('c'))
			)->setBody($teamItemArray);
		$this->broker->flushMessages()->addMessage(
			$commandQuery->getHeaders()->getKey(),
			$this->serializer->serialize($message, 'json')
		)->produceMessage(config(sprintf('broker.topics.answer_%s', $commandQuery->getHeaders()->getSource())));

		Question::succeeded($message, $commandQuery->getHeaders()->getKey());
	}
}