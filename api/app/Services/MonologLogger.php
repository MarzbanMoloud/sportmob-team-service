<?php

namespace App\Services;

use Monolog\Logger;
use Sentry\State\HubInterface;

class MonologLogger extends Logger
{

    public function alert($message, array $context = []): void
    {
        $Sentry = app( HubInterface::class );
        try {
            parent::alert( $message, $context );
        } catch (\Exception $e) {
            $Sentry->captureException( $e->getPrevious() );
        }
    }

}