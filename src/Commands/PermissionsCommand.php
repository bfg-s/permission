<?php

namespace Bfg\Permission\Commands;

use Bfg\Permission\Models\Role;
use Bfg\Permission\UserRepository;
use Illuminate\Console\Command;

/**
 * Class PermissionsCommand.
 * @package Bfg\Permission\Commands
 */
class PermissionsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'permissions {find? : Find word}';

    /**
     * @var UserRepository
     */
    protected UserRepository $repository;

    /**
     * PermissionsCommand constructor.
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
        $rules = $this->repository->factory->globalRules();

        $headers = ['Name', 'Global'];

        $roles = Role::orderBy('priority')->get();

        foreach ($roles as $role) {
            $headers[] = $role->name;
        }

        $this->table(
            $headers,
            collect($rules)->filter(function ($val, $rule) {
                if ($find = $this->argument('find')) {
                    return \Str::is("*{$find}*", $rule);
                }

                return true;
            })->map(function ($val, $rule) use ($roles) {
                $row = [
                    $rule,
                    $this->yes_no($val),
                ];

                foreach ($roles as $role) {
                    $row[] = $this->yes_no(
                        $role->priority ? $this->repository->factory->roleRules($role->slug, true)[$rule] : true
                    );
                }

                return $row;
            })
        );

        return 0;
    }

    /**
     * @param $val
     * @return string
     */
    protected function yes_no($val): string
    {
        return $val ? '<info>Yes</info>' : '<comment>No</comment>';
    }
}
