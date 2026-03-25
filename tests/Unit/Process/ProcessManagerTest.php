<?php

declare(strict_types=1);

use Revolution\Copilot\Process\ProcessManager;

describe('ProcessManager CLI Path Resolution', function () {
    it('resolves COPILOT_CLI_PATH from env when cliPath is null', function () {
        $manager = new ProcessManager(
            cliPath: null,
            env: ['COPILOT_CLI_PATH' => '/custom/path/copilot'],
        );

        $reflection = new ReflectionClass($manager);
        $method = $reflection->getMethod('startProcess');

        // startProcess will fail because the binary doesn't exist,
        // but we can verify cliPath was resolved from env
        try {
            $method->invoke($manager);
        } catch (RuntimeException) {
            // Expected: process won't actually start
        } catch (ErrorException) {
            // Expected: proc_open may fail with posix_spawn error
        }

        $property = $reflection->getProperty('cliPath');
        expect($property->getValue($manager))->toBe('/custom/path/copilot');
    });

    it('prefers explicit cliPath over env COPILOT_CLI_PATH', function () {
        $manager = new ProcessManager(
            cliPath: '/explicit/copilot',
            env: ['COPILOT_CLI_PATH' => '/env/copilot'],
        );

        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('cliPath');

        expect($property->getValue($manager))->toBe('/explicit/copilot');
    });
});

describe('ProcessManager Auth Options', function () {
    it('accepts github_token option', function () {
        $manager = new ProcessManager(
            cliPath: '/test/copilot',
            githubToken: 'gho_test_token',
        );

        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('githubToken');

        expect($property->getValue($manager))->toBe('gho_test_token');
    });

    it('accepts use_logged_in_user option', function () {
        $manager = new ProcessManager(
            cliPath: '/test/copilot',
            useLoggedInUser: false,
        );

        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('useLoggedInUser');

        expect($property->getValue($manager))->toBeFalse();
    });

    it('defaults use_logged_in_user to null', function () {
        $manager = new ProcessManager(
            cliPath: '/test/copilot',
        );

        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('useLoggedInUser');

        expect($property->getValue($manager))->toBeNull();
    });

    it('allows explicit use_logged_in_user true with github_token', function () {
        $manager = new ProcessManager(
            cliPath: '/test/copilot',
            githubToken: 'gho_test_token',
            useLoggedInUser: true,
        );

        $reflection = new ReflectionClass($manager);
        $tokenProperty = $reflection->getProperty('githubToken');
        $userProperty = $reflection->getProperty('useLoggedInUser');

        expect($tokenProperty->getValue($manager))->toBe('gho_test_token')
            ->and($userProperty->getValue($manager))->toBeTrue();
    });
});
