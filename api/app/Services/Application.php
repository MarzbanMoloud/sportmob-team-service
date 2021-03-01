<?php

namespace App\Services;

use Elastica\Client;
use Laravel\Lumen\Application as LumenApplication;
use Monolog\Formatter\ElasticaFormatter;
use Monolog\Handler\ElasticaHandler;
use Monolog\Logger;

class Application extends LumenApplication
{
    const TYPE = 'record';

    protected function registerLogBindings()
    {
        $this->singleton( 'Psr\Log\LoggerInterface',
            function() {
                return new MonologLogger( env( 'APP_NAME' ), [ $this->getMonologHandler() ] );
            } );
    }

	protected function getMonologHandler()
	{
		return (new ElasticaHandler(new Client([
			'host' => config('monolog.handler.elasticSearch.host'),
			'port' => config('monolog.handler.elasticSearch.port'),
			'transport' => config('monolog.handler.elasticSearch.transport'),
			'username' => config('monolog.handler.elasticSearch.username'),
			'password' => config('monolog.handler.elasticSearch.password')
		]), [
			'index' => config('monolog.index'),
			'type' => self::TYPE
		],Logger::ALERT))->setFormatter(new ElasticaFormatter(config('monolog.index'), self::TYPE));
	}
}