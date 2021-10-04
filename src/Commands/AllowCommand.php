<?php

namespace Bfg\Permission\Commands;

/**
 * Class AllowCommand.
 * @package Bfg\Permission\Commands
 */
class AllowCommand extends PermissionAddCommand
{
    /**
     * @var string
     */
    protected $signature = 'allow               {name : The name of permission}              {role_or_user_id? : Role slug or user id in system}              {--r|resource : Resource permission}';

    protected $description = 'Allow gate rule';

    /**
     * @return int
     */
    public function handle(): int
    {
        $this->addRule(true, $this->argument('role_or_user_id'));

        $this->info('Rule successfully allowed!');

        return 0;
    }
}
