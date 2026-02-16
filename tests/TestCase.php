<?php

declare(strict_types=1);

namespace Tests;

use Laravel\Ai\AiServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Revolution\Copilot\CopilotSdkServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CopilotSdkServiceProvider::class,
            AiServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('ai.default', 'copilot');
        $app['config']->set('ai.providers', [
            'copilot' => [
                'driver' => 'copilot',
                'key' => '',
            ],
        ]);
    }
}
