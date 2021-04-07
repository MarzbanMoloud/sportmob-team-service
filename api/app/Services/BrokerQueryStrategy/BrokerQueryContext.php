<?php


namespace App\Services\BrokerQueryStrategy;


use App\Services\BrokerQueryStrategy\Interfaces\BrokerQueryStrategyInterface;
use App\Services\Logger\Question;
use App\ValueObjects\Broker\CommandQuery\Message;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class BrokerQueryContext
 * @package App\Services\BrokerQueryStrategy
 */
class BrokerQueryContext implements BrokerQueryStrategyInterface
{
	private SerializerInterface $serializer;

	/**
	 * BrokerQueryContext constructor.
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
    public function handle(Message $message)
    {
		$strategies = [
			config('broker.services.team_name') => TeamInformation::class,
		];
        Question::received($message, $message->getHeaders()->getKey(), $message->getHeaders()->getSource());

        if ( !isset($strategies[$message->getBody()['entity']]) ) {
            Question::rejected($message, $message->getHeaders()->getKey(), $message->getHeaders()->getSource(), 'lack of ownership');
            return;
        }

        $eventClass = $strategies[$message->getBody()['entity']];
        app($eventClass)->handle($message);
    }
}
