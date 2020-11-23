<?php


namespace App\Providers;


use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandEventInterface;
use App\Services\BrokerInterface;
use App\Services\BrokerQueryStrategy\Interfaces\BrokerQueryEventInterface;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\Services\Kafka\KafkaService;
use Illuminate\Support\ServiceProvider;
use Sentry\SentrySdk;
use SportMob\Translation\Client;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
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
			$normalizers = [new PropertyNormalizer(null, null,  new ReflectionExtractor())];

			return new Serializer($normalizers, $encoders);
		});

    	$this->app->singleton('TranslationClient', function (){
           return (new Client('http://127.0.0.1', 'redis', 6379, SentrySdk::getCurrentHub()));
        });

    	$this->app->singleton(
    	    BrokerInterface::class,
            KafkaService::class
        );

    	/*------ Event Mediator Strategy ------*/
        $this->app->tag([
        ], [EventInterface::TAG_NAME]);

        /*------ Broker Command Strategy ------*/
        $this->app->tag([
        ], [BrokerCommandEventInterface::TAG_NAME]);

        /*------ Broker Query Strategy ------*/
        $this->app->tag([
        ], [BrokerQueryEventInterface::TAG_NAME]);

        /*------ Services ------*/

    }
}
