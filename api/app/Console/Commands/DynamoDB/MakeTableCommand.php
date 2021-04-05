<?php


namespace App\Console\Commands\DynamoDB;


use Illuminate\Console\Command;


/**
 * Class MakeTableCommand
 * @package App\Console\Commands\DynamoDB
 */
class MakeTableCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'make:table';

    /**
     * @var string
     */
    protected $description = 'Make table in DynamoDB.';

    /**
     * MakeTableCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \App\Exceptions\DynamoDB\DynamoDBRepositoryException
     */
    public function handle()
    {
    }
}
