<?php


namespace App\Console\Commands\Consumer;


use App\Console\Commands\Consumer\Traits\CheckCommandArgumentTrait;
use App\Events\Consumer\BrokerCommandEvent;
use App\Services\BrokerInterface;
use Illuminate\Console\Command;


/**
 * Class CommandEventsConsumerCommand
 * @package App\Console\Commands\Consumer
 */
class CommandEventsConsumerCommand extends Command
{
    use CheckCommandArgumentTrait;

    /**
     * @var string
     */
    protected $signature = 'broker:consume:command {timeout=20} {limit=100}';

    /**
     * @var string
     */
    protected $description = 'broker consumer command.';

    private BrokerInterface $broker;

    /**
     * Create a new command instance.
     * @param BrokerInterface $broker
     */
    public function __construct(BrokerInterface $broker)
    {
        $this->broker = $broker;
        parent::__construct();
    }

    public function handle()
    {
        $this->checkValidation();
        $this->broker->addBroker(config('broker.host'))
            ->consumeMessage([config('broker.queues.answer')],
                (int) $this->argument('timeout'),
                app(BrokerCommandEvent::class),
                (int) $this->argument('limit'));
    }
}
