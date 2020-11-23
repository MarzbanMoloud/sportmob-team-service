<?php


namespace App\Console\Commands\Consumer;


use App\Console\Commands\Consumer\Traits\CheckCommandArgumentTrait;
use App\Events\Consumer\BrokerQueryEvent;
use App\Services\BrokerInterface;
use Illuminate\Console\Command;


/**
 * Class QueryEventsConsumerCommand
 * @package App\Console\Commands\Consumer
 */
class QueryEventsConsumerCommand extends Command
{
    use CheckCommandArgumentTrait;

    /**
     * @var string
     */
    protected $signature = 'broker:consume:query {timeout=20} {limit=100}';

    /**
     * @var string
     */
    protected $description = 'broker consume query.';

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
     *
     */
    public function handle()
    {
        $this->checkValidation();
        $this->broker->addBroker(config('broker.host'))
            ->consumeMessage([config('broker.ask.service.request.topic')],
                (int) $this->argument('timeout'),
                app(BrokerQueryEvent::class),
                (int) $this->argument('limit'));
    }
}
