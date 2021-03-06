<?php

namespace Bfg\Permission;

use App\Models\User;
use Bfg\Permission\Models\Role;
use Bfg\Repository\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserRepository.
 * @package Bfg\Permission
 * @method Authenticatable|Model model() Get auth model class
 * @property-read string $model_class Get auth model class
 * @property-read string $guard Get auth guard
 * @property-read string $provider Get auth provider
 * @property-read string $table Get user table
 * @property-read Guard|StatefulGuard $auth Get current auth
 * @property-read Authenticatable|null $user Get authenticatable user model
 * @property-read Role[]|Collection|null $roles Get auth user roles
 * @property-read array $rules The rules of application
 */
class UserRepository extends Repository
{
    /**
     * UserRepository constructor.
     * @param  PermissionFactory  $factory
     */
    public function __construct(
        public PermissionFactory $factory
    ) {
        parent::__construct();
        $this->factory->repository = $this;
    }

    /**
     * @return string|object
     */
    protected function getModelClass(): string|object
    {
        return $this->model_class;
    }

    /**
     * Get auth user roles.
     * @return Role[]|Collection|null
     */
    public function roles(): Collection|null
    {
        if ($this->user) {
            return Role::with('users')->whereHas('users', function ($q) {
                return $q->where('id', $this->user->id);
            })->get();
        }

        return [];
    }

    /**
     * Get current auth.
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    public function auth(): \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
    {
        return \Auth::guard($this->guard);
    }

    /**
     * Get authenticatable user model.
     * @return Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        return $this->auth->user();
    }

    /**
     * Get auth guard.
     * @return string
     */
    public function guard(): string
    {
        return config('auth.defaults.guard');
    }

    /**
     * Get auth provider.
     * @return string
     */
    public function provider(): string
    {
        return config("auth.guards.{$this->guard}.provider");
    }

    /**
     * Get auth model class.
     * @return string
     */
    public function model_class(): string
    {
        return config("auth.providers.{$this->provider}.model", User::class);
    }

    /**
     * Get user table.
     * @return string
     */
    public function table(): string
    {
        return $this->model()->getTable();
    }
}
