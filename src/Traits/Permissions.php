<?php

namespace Bfg\Permission\Traits;

use Bfg\Permission\Models\Role;
use Bfg\Permission\PermissionFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * Trait Permissions for user model.
 * @package Bfg\Permission\Traits
 * @property-read Collection|Role[] $roles All user roles
 */
trait Permissions
{
    /**
     * @var array
     */
    protected static array $user_rules_cache = [];

    /**
     * All user roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_id',
            'role_id'
        );
    }

    /**
     * User rules.
     * @return mixed
     */
    public function rules(): mixed
    {
        if (! array_key_exists($this->id, static::$user_rules_cache)) {
            $factory = app(PermissionFactory::class);

            static::$user_rules_cache[$this->id] = $factory->mergeResultRules(
                $factory->mergeResultRules(
                    $factory->globalRules(),
                    ...$this->roles->sortByDesc('priority')->map(function (Role $role) use ($factory) {
                        return $factory->roleRules($role->slug);
                    })->toArray()
                ),
                $factory->userRules($this->id)
            );
        }

        return static::$user_rules_cache[$this->id];
    }

    /**
     * Get user rule.
     * @param  string  $name
     * @return mixed
     */
    public function rule(string $name): mixed
    {
        $rules = (array) $this->rules();

        return array_key_exists($name, $rules) && $rules[$name];
    }

    /**
     * @param  string  $rule
     * @return bool
     */
    public function allow(string $rule): bool
    {
        return app(PermissionFactory::class)
            ->set(['user-'.$this->id, $rule], true)
            ->save();
    }

    /**
     * @param  string  $rule
     * @return bool
     */
    public function disallow(string $rule): bool
    {
        return app(PermissionFactory::class)
            ->set(['user-'.$this->id, $rule], false)
            ->save();
    }

    /**
     * Check if has role or roles.
     * @param  string  ...$slug
     * @return bool
     */
    public function hasRole(...$slug): bool
    {
        return (bool) $this->roles->whereIn('slug', $slug)->count();
    }

    /**
     * Check if root.
     * @return bool
     */
    public function isRoot(): bool
    {
        return $this->hasRole('root');
    }

    /**
     * Check if admin.
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if moderator.
     * @return bool
     */
    public function isModerator(): bool
    {
        return $this->hasRole('moderator');
    }

    /**
     * Check if user.
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->hasRole('moderator');
    }

    /**
     * Check if guest.
     * @return bool
     */
    public function isGuest(): bool
    {
        return $this->hasRole('guest');
    }
}
