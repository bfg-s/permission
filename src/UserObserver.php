<?php

namespace Bfg\Permission;

use Bfg\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserObserver.
 * @package Bfg\Permission
 */
class UserObserver
{
    public function created(Model $model)
    {
        if ($model->id == 1) {
            $role = Role::where('slug', 'root')->first();
        } elseif ($model->id == 2) {
            $role = Role::where('slug', 'admin')->first();
        } else {
            $role = Role::where('slug', config('permission.registered_role'))->first();
        }

        if ($role) {
            $role->users()->attach([$model->id]);
        }
    }
}
