<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\CacheServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Revolution\Copilot\CopilotSdkServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CacheServiceProvider::class,
            CopilotSdkServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Configure cache for testing
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cache.stores.array', [
            'driver' => 'array',
            'serialize' => false,
        ]);

        // Manually register cache if not already registered (for deferred providers)
        if (! $app->bound('cache')) {
            $app->singleton('cache', function ($app) {
                return new CacheManager($app);
            });
        }
    }
}
