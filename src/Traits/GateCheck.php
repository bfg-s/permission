<?php

namespace Bfg\Permission\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait GateCheck.
 * @package Bfg\Permission\Traits
 */
trait GateCheck
{
    /**
     * Open gate for access.
     *
     * @param  string  $rule
     * @param  Model|User  $user
     * @param  Model  $model
     * @return bool
     */
    public function gateCheck(string $rule, Model $user, Model $model)
    {
        return true;
    }
}
