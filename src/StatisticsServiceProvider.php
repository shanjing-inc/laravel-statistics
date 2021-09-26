<?php

namespace Shanjing\LaravelStatistics;

use Illuminate\Support\ServiceProvider;

class StatisticsServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * @var array
     */
    protected $commands = [
        Console\PublishCommand::class,
        Console\InstallCommand::class,

    ];

    public function register()
    {
        $this->commands($this->commands);

        $this->app->singleton(Statistics::class, function () {
            return new Statistics();
        });

        $this->app->alias(Statistics::class, 'statistics');
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishing();
    }

    /**
     * 资源发布注册.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config' => config_path()], 'shanjing-statistics-config');
            $this->publishes(
                [__DIR__ . '/../database/migrations' => database_path('migrations')],
                'shanjing-statistics-migrations'
            );
        }
    }


    public function provides()
    {
        return [Statistics::class, 'statistics'];
    }
}
