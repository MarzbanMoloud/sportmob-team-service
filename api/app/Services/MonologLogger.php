<?php

namespace App\Services;

use Monolog\Logger;
use Sentry\State\HubInterface;

class MonologLogger extends Logger
{

    public function alert($message, array $context = []): void
    {
        $Sentry = app( HubInterface::class );
        $EditedContext  = array_combine(
            array_map( function($k) {
                return sprintf( "%s_%s", strtolower(env( 'APP_NAME' )), $k );
            },
                array_keys( $context ) ),
            $context
        );
        try {
            parent::alert( $message, $EditedContext );
        } catch (\Exception $e) {
            $Sentry->captureException( $e->getPrevious() );
        }
    }

}