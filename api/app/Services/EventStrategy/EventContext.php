<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/28/2020
 * Time: 10:59 AM
 */

namespace App\Services\EventStrategy;


use App\Services\EventStrategy\Interfaces\EventStrategyInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\Message;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class EventContext
 * @package App\Services\EventStrategy
 */
class EventContext implements EventStrategyInterface
{
	private SerializerInterface $serializer;

	/**
	 * EventContext constructor.
	 * @param SerializerInterface $serializer
	 */
	public function __construct(SerializerInterface $serializer)
	{
		$this->serializer = $serializer;
	}

    /**
     * @param $message
     * @return mixed|void
     */
    public function handle(Message $message)
    {
		$strategies = [
			config('mediator-event.events.team_was_created') => TeamWasCreated::class,
			config('mediator-event.events.team_was_updated') => TeamWasUpdated::class,
			config('mediator-event.events.player_was_transferred') => PlayerWasTransferred::class,
			config('mediator-event.events.team_became_runner_up') => TeamBecameRunnerUp::class,
			config('mediator-event.events.team_became_winner') => TeamBecameWinner::class,
			config('mediator-event.events.match_was_created') => MatchWasCreated::class,
			config('mediator-event.events.match_finished') => MatchFinished::class,
			config('mediator-event.events.match_status_changed') => MatchStatusChanged::class,
		];
        Event::received($message, $message->getHeaders()->getEvent());

        if ( !isset($strategies[$message->getHeaders()->getEvent()]) ) {
            Event::rejected($message, $message->getHeaders()->getEvent(), 'lack of ownership');
            return;
        }

        $eventClass = $strategies[$message->getHeaders()->getEvent()];
        app($eventClass)->handle($message->getBody());
    }
}
