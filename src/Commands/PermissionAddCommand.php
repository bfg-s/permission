<?php

namespace Bfg\Permission\Commands;

use Bfg\Permission\UserRepository;
use Illuminate\Console\Command;

/**
 * Class PermissionAddCommand.
 * @package Bfg\Permission\Commands
 */
class PermissionAddCommand extends Command
{
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
     * Add rule to factory.
     * @param  bool  $permission
     * @param  string|null  $role_or_user_id
     */
    protected function addRule(bool $permission = true, string $role_or_user_id = null)
    {
        $name = $this->argument('name');

        $resource = $this->option('resource');

        $depth = $role_or_user_id ? (is_numeric($role_or_user_id) ? "user-{$role_or_user_id}" : "role-{$role_or_user_id}") : 'global';

        if ($resource) {
            $types = [
                'viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete',
            ];

            foreach ($types as $type) {
                $this->repository->factory->set([$depth, $type.'-'.$name], $permission);
            }
        } else {
            $this->repository->factory->set([$depth, $name], $permission);
        }

        $this->repository->factory->save();
    }
}
