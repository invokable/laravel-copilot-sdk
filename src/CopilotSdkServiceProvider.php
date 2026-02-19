<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Ai\Ai;
use Revolution\Copilot\Ai\CopilotGateway;
use Revolution\Copilot\Ai\CopilotProvider;
use Revolution\Copilot\Contracts\Factory;

use function Orchestra\Testbench\default_skeleton_path;

class CopilotSdkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/copilot.php', 'copilot');

        $this->app->scoped(Factory::class, function ($app) {
            return new CopilotManager($app['config']['copilot'] ?? []);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/copilot.php' => config_path('copilot.php'),
            ], 'copilot-config');
        }

        if (class_exists(Ai::class)) {
            Ai::extend('copilot', function (Application $app, array $config) {
                return new CopilotProvider(
                    new CopilotGateway($this->app['events']),
                    $config,
                    $this->app->make(Dispatcher::class),
                );
            });
        }

        if (defined('TESTBENCH_CORE')) {
            $this->testbench();
        }
    }

    /**
     * Fixed a path issue when running testbench.
     */
    protected function testbench(): void
    {
        Event::listen(function (CommandStarting $event) {
            if (! Str::startsWith($event->command, 'copilot:')) {
                return;
            }

            // Change the base path from the testbench skeleton to the current working directory.
            $this->app->setBasePath(getcwd());

            $this->app->useStoragePath(default_skeleton_path('storage'));
            $this->app->useAppPath(default_skeleton_path('app'));
            // Add any necessary paths other than storage and app.
        });
    }
}
