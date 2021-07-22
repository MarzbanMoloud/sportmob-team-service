<?php


namespace App\Services\BrokerCommandStrategy;


use App\Listeners\Projection\MatchWasCreatedProjectorListener;
use App\Listeners\Projection\MembershipWasUpdatedProjectorListener;
use App\Listeners\Projection\TrophyProjectorListener;
use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandStrategyInterface;
use App\Services\Logger\Answer;
use App\ValueObjects\Broker\CommandQuery\Message;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class BrokerCommandContext
 * @package App\Services\BrokerCommandStrategy
 */
class BrokerCommandContext implements BrokerCommandStrategyInterface
{
	private SerializerInterface $serializer;

	/**
	 * BrokerCommandContext constructor.
	 * @param SerializerInterface $serializer
	 */
	public function __construct(SerializerInterface $serializer)
	{
		$this->serializer = $serializer;
	}

    /**
     * @param Message $message
     * @return mixed|void
     */
    public function handle(Message $message): void
    {
		$strategies = [
			MembershipWasUpdatedProjectorListener::BROKER_EVENT_KEY => MembershipWasUpdatedUpdateInfo::class,
			TrophyProjectorListener::BROKER_EVENT_KEY => TrophyUpdateInfo::class,
			MatchWasCreatedProjectorListener::BROKER_EVENT_KEY => MatchWasCreatedUpdatedInfo::class,
		];
        Answer::received($message, $message->getHeaders()->getKey(), $message->getHeaders()->getSource());

        if ( !isset($strategies[$message->getHeaders()->getKey()]) ) {
            Answer::rejected($message, $message->getHeaders()->getKey(), $message->getHeaders()->getSource(), 'lack of ownership');
            return;
        }

        $eventClass = $strategies[$message->getHeaders()->getKey()];
        app($eventClass)->handle($message);
    }
}
