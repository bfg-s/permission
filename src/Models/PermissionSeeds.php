<?php

namespace Bfg\Permission\Models;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Class PermissionSeeds.
 * @package Bfg\Permission\Models
 */
class PermissionSeeds extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        if (! Role::where('slug', 'root')->exists()) {
            Role::create(['name' => 'Root', 'slug' => 'root', 'priority' => '']);
        }
        if (! Role::where('slug', 'admin')->exists()) {
            Role::create(['name' => 'Administrator', 'slug' => 'admin', 'priority' => '']);
        }
        if (! Role::where('slug', 'moderator')->exists()) {
            Role::create(['name' => 'Moderator', 'slug' => 'moderator', 'priority' => '']);
        }
        if (! Role::where('slug', 'user')->exists()) {
            Role::create(['name' => 'User', 'slug' => 'user', 'priority' => '']);
        }
        if (! Role::where('slug', 'guest')->exists()) {
            Role::create(['name' => 'Guest', 'slug' => 'guest', 'priority' => '']);
        }
        foreach (Role::all() as $role) {
            $users_with_role_exists = User::whereHas('roles', function ($q) use ($role) {
                return $q->where('slug', $role->slug);
            })->exists();

            if (! $users_with_role_exists) {

                /** @var User $user */
                $user = User::where([
                    'name' => $role->slug,
                    'email' => $role->slug.'@app.com',
                ])->first();

                if (! $user) {
                    $user = User::factory()->createOne([
                        'name' => $role->slug,
                        'email' => $role->slug.'@app.com',
                        'password' => bcrypt($role->slug),
                    ]);
                }

                $user->roles()->sync($role->id);
            }
        }
    }
}
