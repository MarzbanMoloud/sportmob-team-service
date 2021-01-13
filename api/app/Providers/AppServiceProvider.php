<?php


namespace App\Providers;


use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Services\AWS\BrokerService;
use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandEventInterface;
use App\Services\BrokerCommandStrategy\PlayerWasTransferredUpdateInfo;
use App\Services\BrokerInterface;
use App\Services\BrokerQueryStrategy\Interfaces\BrokerQueryEventInterface;
use App\Services\Cache\BrokerMessageCacheService;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\Services\Cache\TeamCacheService;
use App\Services\Cache\TransferCacheService;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\Services\EventStrategy\PlayerWasTransferred;
use App\Services\EventStrategy\TeamBecameRunnerUp;
use App\Services\EventStrategy\TeamBecameWinner;
use App\Services\EventStrategy\TeamWasCreated;
use Illuminate\Support\ServiceProvider;
use Sentry\SentrySdk;
use SportMob\Translation\Client;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;


/**
 * Class AppServiceProvider
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    	$this->app->bind('Serializer', function (){
			$encoders = [ new JsonEncoder()];
            $normalizers = [new PropertyNormalizer(null,null,new ReflectionExtractor()), new DateTimeNormalizer()];

			return new Serializer($normalizers, $encoders);
		});

    	$this->app->singleton('TranslationClient', function (){
           return (new Client('http://127.0.0.1', 'redis', 6379, SentrySdk::getCurrentHub()));
        });

    	$this->app->singleton(
    	    BrokerInterface::class,
            BrokerService::class
        );

    	/*------ Event Mediator Strategy ------*/
        $this->app->tag([
        	TeamWasCreated::class,
			PlayerWasTransferred::class,
			TeamBecameRunnerUp::class,
			TeamBecameWinner::class
        ], [EventInterface::TAG_NAME]);

        /*------ Broker Command Strategy ------*/
        $this->app->tag([
        	PlayerWasTransferredUpdateInfo::class
        ], [BrokerCommandEventInterface::TAG_NAME]);

        /*------ Broker Query Strategy ------*/
        $this->app->tag([
        ], [BrokerQueryEventInterface::TAG_NAME]);

        /*------ Services ------*/
		$this->app->singleton(
			ResponseServiceInterface::class,
			ResponseService::class
		);

		$this->app->singleton(
			TeamCacheServiceInterface::class,
			TeamCacheService::class
		);

		$this->app->singleton(
			BrokerMessageCacheServiceInterface::class,
			BrokerMessageCacheService::class
		);

		$this->app->singleton(
			TransferCacheServiceInterface::class,
			TransferCacheService::class
		);
    }
}
