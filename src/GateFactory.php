<?php

namespace Bfg\Permission;

use Bfg\Permission\Traits\Permissions;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class GateFactory.
 * @package Bfg\Permission
 */
class GateFactory
{
    /**
     * @var string
     */
    protected string $user_eq_field = 'id';

    /**
     * @var string
     */
    protected string $model_eq_field = 'user_id';

    /**
     * GateFactory constructor.
     *
     * @param  Gate  $gate
     * @param  array  $rules
     */
    public function __construct(Gate $gate, array $rules)
    {
        $this->user_eq_field = config('permission.user_eq_field', $this->user_eq_field);

        $this->model_eq_field = config('permission.model_eq_field', $this->model_eq_field);

        $gate->before(
            fn (...$params) => call_user_func([$this, 'priorityRoot'], ...$params)
        );

        foreach ($rules as $rule) {
            $gate->define(
                $rule,
                fn (...$params) => call_user_func([$this, 'rule'], $rule, ...$params)
            );
        }

        foreach ([
            'viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete',
        ] as $event) {
            $gate->define(
                $event,
                fn (...$params) => call_user_func([$this, 'shortTrap'], $event, ...$params)
            );
        }
    }

    /**
     * Short trap for model access tests.
     *
     * @param  string  $rule
     * @param  Model  $model
     * @param  null  $obj
     * @param  bool  $or
     * @return bool
     */
    protected function shortTrap(string $rule, Model $model, $obj = null, bool $or = false): bool
    {
        if ($obj instanceof Model || is_string($obj)) {
            $rule = $rule.'-'.Str::snake(class_basename($obj));
        }

        return $this->rule($rule, $model, $obj, $or);
    }

    /**
     * Check root access.
     *
     * @param  Model  $model
     * @return bool|null
     */
    protected function priorityRoot(Model $model): ?bool
    {
        $roleable = method_exists($model, 'roles');

        if ($roleable && collect($model->roles)->where('priority', 0)->count()) {
            return true;
        }

        return null;
    }

    /**
     * Gate rule accessor.
     *
     * @param  string  $rule
     * @param  Model  $model
     * @param  null  $obj
     * @param  bool  $or
     * @return bool
     */
    protected function rule(string $rule, Model $model, $obj = null, bool $or = false): bool
    {
        /** @var Model|Permissions $model */
        $role_result = ! method_exists($model, 'rule') || $model->rule($rule);

        if ($obj !== null) {
            if (is_object($obj)) {
                if (method_exists($obj, 'gateCheck')) {
                    return $obj->gateCheck($rule, $model, $obj);
                }
                if ($obj instanceof Model) {
                    if ($obj->exists) {
                        return $or ? ($model->{$this->user_eq_field} == $obj->{$this->model_eq_field} || $role_result) :
                            ($model->{$this->user_eq_field} == $obj->{$this->model_eq_field} && $role_result);
                    }
                } else {
                    return $or ? ($model->{$this->user_eq_field} == $obj->{$this->model_eq_field} || $role_result) :
                        ($model->{$this->user_eq_field} == $obj->{$this->model_eq_field} && $role_result);
                }
            } elseif (is_array($obj)) {
                return $or ? ($model->{$this->user_eq_field} == $obj[$this->model_eq_field] || $role_result) :
                    ($model->{$this->user_eq_field} == $obj[$this->model_eq_field] && $role_result);
            }
        }

        return $role_result;
    }

    /**
     * @return GateFactory|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public static function create()
    {
        \Auth::shouldUse(config('auth.defaults.guard'));

        return app(static::class, [
            'rules' => app(PermissionFactory::class)->rules(),
        ]);
    }
}
