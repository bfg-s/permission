<?php

namespace Bfg\Permission;

use Bfg\Entity\ConfigFactory;
use Bfg\Permission\Models\Role;

/**
 * Permissions store.
 * @package Bfg\Permission
 */
class PermissionFactory extends ConfigFactory
{
    /**
     * Repository of user.
     * @var UserRepository
     */
    public UserRepository $repository;

    /**
     * PermissionFactory constructor.
     */
    public function __construct()
    {
        parent::__construct(config('permission.store'));
    }

    /**
     * @param  string  $name
     * @return $this
     */
    public function addRule(string $name): static
    {
        $this->set(['global', $name], true)
            ->save();

        return $this;
    }

    /**
     * List of rules.
     * @return array
     */
    public function rules(): array
    {
        return array_keys($this->globalRules());
    }

    /**
     * Get all rules with default access.
     * @return array
     */
    public function globalRules(): array
    {
        return $this->get('global', []);
    }

    /**
     * Get role rules.
     * @param  string  $role
     * @param  bool  $global
     * @return mixed
     */
    public function roleRules(string $role, bool $global = false): mixed
    {
        $result = $this->get(["role-{$role}"], []);

        return $global ? $this->mergeResultRules($this->globalRules(), $result) : $result;
    }

    /**
     * Get user rules.
     * @param  int  $user_id
     * @return array
     */
    public function userRules(int $user_id): array
    {
        return $this->get(["user-{$user_id}"], []);
    }

    /**
     * The update global rules.
     * @param  array  $rules
     * @return bool
     */
    public function updateGlobalRules(array $rules): bool
    {
        $this->set(
            'global',
            $this->mergeResultRules(
                $this->globalRules(),
                $rules
            )
        );

        return $this->save();
    }

    /**
     * The update role rules.
     * @param  string  $role
     * @param  array  $rules
     * @return bool
     */
    public function updateRoleRules(string $role, array $rules): bool
    {
        $this->set(
            "role-{$role}",
            $this->mergeResultRules(
                $this->globalRules(),
                $rules
            )
        );

        return $this->save();
    }

    /**
     * The update user rules.
     * @param  int  $user_id
     * @param  array  $rules
     * @return bool
     */
    public function updateUserRules(int $user_id, array $rules): bool
    {
        $this->set(
            "user-{$user_id}",
            $this->mergeResultRules(
                $this->globalRules(),
                $rules
            )
        );

        return $this->save();
    }

    /**
     * Merge rules.
     * @param  mixed  ...$array
     * @return array
     */
    public function mergeResultRules(array ...$array): array
    {
        $arr = [];

        foreach ($array as $i => $array_next) {
            foreach ($array_next as $key => $item) {
                $arr[$key] = $item;
            }
        }

        return $arr;
    }
}
