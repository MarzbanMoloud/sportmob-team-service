<?php


namespace App\Console\Commands\Consumer;


use App\Console\Commands\Consumer\Traits\CheckCommandArgumentTrait;
use App\Events\Consumer\BrokerMediatorEvent;
use App\Services\BrokerInterface;
use Illuminate\Console\Command;


/**
 * Class MediatorEventsConsumerCommand
 * @package App\Console\Commands\Consumer
 */
class MediatorEventsConsumerCommand extends Command
{
    use CheckCommandArgumentTrait;

    /**
     * @var string
     */
    protected $signature = 'broker:consume:mediator {timeout=20} {limit=100}';

    /**
     * @var string
     */
    protected $description = 'broker consume mediator.';

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

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->checkValidation();
        $this->broker->consumeMessage([config('broker.queues.event')],
                (int) $this->argument('timeout'),
                app(BrokerMediatorEvent::class),
                (int) $this->argument('limit'));
    }
}
