<?php

namespace Bfg\Permission\Commands;

use Bfg\Permission\UserRepository;
use Illuminate\Console\Command;

/**
 * Class PermissionDeleteCommand.
 * @package Bfg\Permission\Commands
 */
class PermissionDeleteCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'permission:delete               {name : The name of permission}              {--r|resource : Make resource permission}';

    /**
     * @var UserRepository
     */
    protected UserRepository $repository;

    /**
     * PermissionAddCommand constructor.
     * @param  UserRepository  $repository
     */
    public function __construct(UserRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * @return int
     */
    public function handle(): int
    {
        $name = $this->argument('name');

        $resource = $this->option('resource');

        if ($resource) {
            $types = [
                'viewAny', 'view', 'create', 'create', 'update', 'update', 'delete',
            ];

            foreach ($types as $type) {
                $this->repository->factory->forget(['global', $type.'-'.$name]);
            }
        } else {
            $this->repository->factory->forget(['global', $name]);
        }

        $this->repository->factory->save();

        $this->info('Permission successfully deleted!');

        return 0;
    }
}
