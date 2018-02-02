<?php

namespace ITBrains\ReleaseScript;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use ITBrains\ReleaseScript\Console\InstallCommand;
use ITBrains\ReleaseScript\Console\MakeCommand;
use ITBrains\ReleaseScript\Console\RunCommand;
use ITBrains\ReleaseScript\Console\StatusCommand;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRepository();

        $this->mergeConfigFrom(
            __DIR__ . '/config.php', 'release_script'
        );
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeCommand::class,
                InstallCommand::class,
                RunCommand::class,
                StatusCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/config.php' => config_path('release_script.php'),
        ]);
    }

    /**
     * Register the migration repository service.
     *
     * @return void
     */
    protected function registerRepository()
    {
        $this->app->singleton('release_script.repository', function ($app) {
            $table = $app['config']['release_script.table'];

            return new DatabaseScriptRepository($app['db'], $table);
        });

        $this->app->bind(ScriptRepositoryInterface::class, function () {
            return $this->app->make('release_script.repository');
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'release_script.repository',
        ];
    }
}
