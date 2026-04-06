<?php

namespace ByteTCore\Serpo;

use ByteTCore\Serpo\Commands\MakeCriteriaCommand;
use ByteTCore\Serpo\Commands\MakeRepositoryCommand;
use ByteTCore\Serpo\Commands\MakeServiceCommand;
use Illuminate\Support\ServiceProvider;

class SerpoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/Config/serpo.php', 'serpo');
    }

    public function boot(): void
    {
        $this->publishes(
            [__DIR__.'/Config/serpo.php' => config_path('serpo.php')],
            'serpo-config'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeRepositoryCommand::class,
                MakeServiceCommand::class,
                MakeCriteriaCommand::class,
            ]);
        }
    }
}
