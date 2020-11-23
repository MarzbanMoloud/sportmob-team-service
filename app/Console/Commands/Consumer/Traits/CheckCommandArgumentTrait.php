<?php


namespace App\Console\Commands\Consumer\Traits;


/**
 * Trait CheckCommandArgumentTrait
 * @package App\Console\Commands\Consumer\Traits
 */
trait CheckCommandArgumentTrait
{
    /**
     * @throws \Exception
     */
    public function checkValidation(): void
    {
        $arguments = $this->arguments();
        unset($arguments['command']);
        foreach ($arguments as $argument => $value) {
            if (!is_numeric($value)) {
                throw new \Exception(sprintf('%s argument is invalid.', $argument));
            }
        }
    }
}
