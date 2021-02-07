<?php


namespace App\Services;


use App\Services\Monolog\SportmobFormatter;
use Elastica\Client;
use Laravel\Lumen\Application as LumenApplication;
use Monolog\Handler\ElasticaHandler;
use Monolog\Logger;


class Application extends LumenApplication
{
	const TYPE = 'record';
	protected $ranServiceBinders = [];

	/**
	 * A custom callback used to configure Monolog.
	 *
	 * @var callable|null
	 */
	protected $monologConfigurator;

	protected function registerLogBindings()
	{
		$this->singleton('Psr\Log\LoggerInterface', function () {
			if ($this->monologConfigurator) {
				return call_user_func($this->monologConfigurator, new Logger('lumen'));
			} else {
				return new Logger('lumen', [$this->getMonologHandler()]);
			}
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureMonologUsing(callable $callback)
	{
		$this->monologConfigurator = $callback;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
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
		]))->setFormatter(new SportmobFormatter(config('monolog.index'), self::TYPE));
	}
}