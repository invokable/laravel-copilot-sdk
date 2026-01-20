<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Illuminate\Support\ServiceProvider;
use Revolution\Copilot\Contracts\Factory;

class CopilotSdkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/copilot.php', 'copilot');

        $this->app->singleton(Factory::class, function ($app) {
            return new CopilotManager($app['config']['copilot'] ?? []);
        });

        $this->app->alias(Factory::class, 'copilot');
        $this->app->alias(Factory::class, CopilotManager::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/copilot.php' => config_path('copilot.php'),
            ], 'copilot-config');
        }
    }
}
