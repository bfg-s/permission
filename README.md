# Extension permission

## Install
1. Install as dependency:
```bash
composer require bfg/permission
```

2. Set up for mode `Permission` trait:
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\User\UserHasRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Bfg\Permission\Traits\Permissions;

/**
 * User Class
 * @package App\Models
 */
class User extends Authenticatable
{
    use Notifiable, 
        UserHasRole, 
        HasFactory, 
        Permissions; // Like this
    
    // ...
}
```

3. Install resources and tables:
```bash
php artisan install bfg/permission
```

## About the concept.
The ability to create and monitor the rules for the 
[`Laravel gates`](https://laravel.com/docs/8.x/authorization#gates) with 
the distribution on the role and the ability to control it conveniently.
Also comes with a user role model and a list of ready-made roles, 
such as: `Root`, `Administrator`, `Moderator`, `User`, `Guest`.

## Description
The package has a hierarchy of roles and can distribute access between 
them, using the [`Laravel gates`](https://laravel.com/docs/8.x/authorization#gates) 
system. The warehouse for the gate is simply "PHP" file with an array in 
the "Storage" folder.

## Where can I use it?
Always when you want to use the authorization control system "Laravel" 
through the gate, but do not want to cheat a whole bunch of rumbled rows 
with a gate or create a bunch of files with politicians if you need to 
control all your gates and disable or switch them between the roles of users.

## Publish
Publish configs
```bash
php artisan vendor:publish --tag=permission-config
```
Publish migrations
```bash
php artisan vendor:publish --tag=permission-migrations
```

## Commands
Commands for managing access and distribution of them between roles.
### Permission list
In order to display a full list of rules for the gate.
```bash
Usage:
  permissions [<find>]

Arguments:
  find                  Find word
```
But it happens so that the rules becomes too much and on 
this can be used by searching, specifying the search word 
immediately after the command, for example:
```bash
php artisan permissions message
```
Output:
```bash
+---------------------+--------+---------------+-----------+------+-------+
| Name                | Global | Administrator | Moderator | User | Guest |
+---------------------+--------+---------------+-----------+------+-------+
| viewAny-message     | Yes    | No            | Yes       | Yes  | Yes   |
| view-message        | Yes    | No            | Yes       | Yes  | Yes   |
| create-message      | Yes    | No            | Yes       | Yes  | Yes   |
| update-message      | Yes    | No            | Yes       | Yes  | Yes   |
| delete-message      | Yes    | No            | Yes       | Yes  | Yes   |
| restore-message     | Yes    | No            | Yes       | Yes  | Yes   |
| forceDelete-message | Yes    | No            | Yes       | Yes  | Yes   |
+---------------------+--------+---------------+-----------+------+-------+
```

### Allow or add permission
To add immediately with open access or open access 
to the rule, you must use this command.
```bash
Usage:
  allow [options] [--] <name> [<role_or_user_id>]

Arguments:
  name                  The name of permission
  role_or_user_id       Role slug or user id in system

Options:
  -r, --resource        Resource permission
```
If you create a rule as a resource:
```bash
php artisan allow message -r
```
You will be created 7 rules with the name you indicated, namely:
`viewAny-message`, `view-message`, `create-message`, `update-message`, 
`delete-message`, `restore-message`, `forceDelete-message`

In order to manage access for a role or for a user, you can add the 
following parameter that calls for the user ID or role name:
```bash
php artisan allow message guest -r
```
Opens access to all communication resources for the guest.

### Disallow or add permission
To immediately add with closed access or close access to the 
rule, you must use this command.
```bash
Usage:
  disallow [options] [--] <name> [<role_or_user_id>]

Arguments:
  name                  The name of permission
  role_or_user_id       Role slug or user id in system

Options:
  -r, --resource        Resource permission
```
All the logic of the team is identical to the opening team.
```bash
php artisan disallow message guest -r
```

### Delete permission
To remove the rules of the gate from the general list.
```bash
Usage:
  permission:delete [options] [--] <name>

Arguments:
  name                  The name of permission

Options:
  -r, --resource        Make resource permission
```
```bash
php artisan permission:delete view-message
```
You can delete one rule or immediately all its resources using the resource flag:
```bash
php artisan permission:delete message -r
```

## How to use?
All rules created by you automatically fall into the system of the gate 
of "Laravel" and in this can be used as before you used the system 
of the [`Laravel gates`](https://laravel.com/docs/8.x/authorization#gates).
```php
<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    /**
     * Update the given message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        if (! Gate::allows('update-message', $message)) {
            abort(403);
        }

        // Update the message...
    }
}
```
```php
if (Gate::forUser($user)->allows('update-message', $message)) {
    // The user can update the message...
}

if (Gate::forUser($user)->denies('update-message', $message)) {
    // The user can't update the message...
}
```
```php
if (Gate::any(['update-post', 'delete-message'], $message)) {
    // The user can update or delete the message...
}

if (Gate::none(['update-post', 'delete-message'], $message)) {
    // The user can't update or delete the message...
}
```
```php
Gate::authorize('update-message', $message);
```
> Important! If you transmit as a parameter to the gate model that is recovered, 
> the rules will check the field `user_id` and `id` user-friendly gate. 
> These fields are configured in the settings `user_eq_field` and `model_eq_field`. 
> Or you can write a class verification rule by adding the 
> `gateCheck(string $rule, Model $user, Model $model)` method into it.
