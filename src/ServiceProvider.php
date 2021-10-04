<?php

namespace Bfg\Permission;

use App\Models\User;
use Bfg\Installer\Processor\InstallProcessor;
use Bfg\Installer\Processor\UnInstallProcessor;
use Bfg\Installer\Providers\InstalledProvider;
use Bfg\Permission\Commands\AllowCommand;
use Bfg\Permission\Commands\DisallowCommand;
use Bfg\Permission\Commands\PermissionDeleteCommand;
use Bfg\Permission\Commands\PermissionsCommand;
use Bfg\Permission\Models\PermissionSeeds;
use Bfg\Permission\Models\Role;
use Bfg\Permission\Traits\Permissions;
use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\Jetstream;

/**
 * Class ServiceProvider.
 * @package Bfg\Permission
 */
class ServiceProvider extends InstalledProvider
{
    /**
     * Set as installed by default.
     * @var bool
     */
    public bool $installed = true;

    /**
     * Register route settings.
     * @return void
     * @throws \ReflectionException
     */
    public function register()
    {
        parent::register();

        /**
         * Register commands.
         */
        $this->commands([
            PermissionsCommand::class,
            AllowCommand::class,
            DisallowCommand::class,
            PermissionDeleteCommand::class,
        ]);
    }

    public function boot()
    {
        /**
         * Merge config from having by default.
         */
        $this->mergeConfigFrom(
            __DIR__.'/../config/permission.php', 'permission'
        );

        /**
         * Register publisher scaffold configs.
         */
        $this->publishes([
            __DIR__.'/../config/permission.php' => config_path('permission.php'),
        ], 'permission-config');

        /**
         * Register publisher migrations.
         */
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('/migrations'),
        ], 'permission-migrations');

        parent::boot();
    }

    /**
     * Executed when the provider is registered
     * and the extension is installed.
     * @return void
     */
    public function installed(): void
    {
        $this->app->singleton(UserRepository::class, function () {
            return new UserRepository(
                app(PermissionFactory::class)
            );
        });

        call_user_func(
            [app(UserRepository::class)->model_class, 'observe'],
            UserObserver::class
        );

        GateFactory::create();
    }

    /**
     * Executed when the provider run method
     * "boot" and the extension is installed.
     * @return void
     */
    public function run(): void
    {
        //
    }

    /**
     * Run on install extension.
     * @param  InstallProcessor  $processor
     */
    public function install(InstallProcessor $processor)
    {
        parent::install($processor);

        $processor->command->call('vendor:publish', [
            '--tag' => 'permission-config',
        ]);

        if (! \Schema::hasTable('roles')) {
            $processor->publish(__DIR__.'/../database/migrations', database_path('migrations'));

            $processor->command->call('db:seed', [
                '--class' => PermissionSeeds::class,
            ]);
        }
    }

    /**
     * @param  UnInstallProcessor  $processor
     */
    public function uninstall(UnInstallProcessor $processor)
    {
        parent::uninstall($processor);

        $processor->unpublish(__DIR__.'/../database/migrations', database_path('migrations'));
    }
}
