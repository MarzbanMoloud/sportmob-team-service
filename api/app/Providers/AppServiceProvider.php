<?php


namespace App\Providers;


use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Services\AWS\BrokerService;
use App\Services\BrokerInterface;
use App\Services\Cache\BrokerMessageCacheService;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\Services\Cache\Interfaces\TeamsMatchCacheServiceInterface;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\Services\Cache\Interfaces\TrophyCacheServiceInterface;
use App\Services\Cache\TeamCacheService;
use App\Services\Cache\TeamsMatchCacheService;
use App\Services\Cache\TransferCacheService;
use App\Services\Cache\TrophyCacheService;
use Illuminate\Support\ServiceProvider;
use Sentry\SentrySdk;
use SportMob\Translation\Client;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;


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
        $this->app->singleton(SerializerInterface::class, function (){
            $encoders = [ new JsonEncoder()];
            $extractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);
            $normalizers = [new PropertyNormalizer(null,null,$extractor), new DateTimeNormalizer(), new ArrayDenormalizer(),];
            return new Serializer($normalizers, $encoders);
        });

        $this->app->singleton( Client::class,
            function() {
                return new Client( env( 'TRANSLATION_SERVICE_URL', 'http://127.0.0.1' ),
                                   env( 'REDIS_HOST', 'redis' ),
                                   env( 'REDIS_PORT', 6379 ),
                                   SentrySdk::getCurrentHub() );
            } );

    	$this->app->singleton(
    	    BrokerInterface::class,
            BrokerService::class
        );

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

		$this->app->singleton(
			TrophyCacheServiceInterface::class,
			TrophyCacheService::class
		);

		$this->app->singleton(
			TeamsMatchCacheServiceInterface::class,
			TeamsMatchCacheService::class
		);
    }
}
