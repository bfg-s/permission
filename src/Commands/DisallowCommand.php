<?php

namespace Bfg\Permission\Commands;

/**
 * Class AllowCommand.
 * @package Bfg\Permission\Commands
 */
class DisallowCommand extends PermissionAddCommand
{
    /**
     * @var string
     */
    protected $signature = 'disallow               {name : The name of permission}              {role_or_user_id? : Role slug or user id in system}              {--r|resource : Resource permission}';

    protected $description = 'Disallow gate rule';

    /**
     * @return int
     */
    public function handle(): int
    {
        $this->addRule(false, $this->argument('role_or_user_id'));

        $this->info('Rule successfully disallowed!');

        return 0;
    }
}
