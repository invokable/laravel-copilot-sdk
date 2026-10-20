<?php

declare(strict_types=1);

use Revolt\EventLoop;
use Revolution\Copilot\Enums\CopilotClientMode;
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
            gitHubToken: 'gho_test_token',
        );

        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('gitHubToken');

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
            gitHubToken: 'gho_test_token',
            useLoggedInUser: true,
        );

        $reflection = new ReflectionClass($manager);
        $tokenProperty = $reflection->getProperty('gitHubToken');
        $userProperty = $reflection->getProperty('useLoggedInUser');

        expect($tokenProperty->getValue($manager))->toBe('gho_test_token')
            ->and($userProperty->getValue($manager))->toBeTrue();
    });
});

describe('ProcessManager process diagnostics', function () {
    it('retains only the latest 8 KiB of stderr output after the process stops', function () {
        $manager = new ProcessManager(
            cliPath: PHP_BINARY,
            cliArgs: [
                '-r',
                'fwrite(STDERR, str_repeat("x", 9000)."runtime diagnostic\n"); while (! feof(STDIN)) { usleep(1000); }',
                '--',
            ],
        );

        try {
            $manager->start();
            EventLoop::delay(0.05, fn () => EventLoop::getDriver()->stop());
            EventLoop::run();
        } finally {
            $manager->stop();
        }

        $stderrOutput = $manager->getStderrOutput();

        expect(strlen($stderrOutput) <= 8192)->toBeTrue()
            ->and(str_ends_with($stderrOutput, 'runtime diagnostic'))->toBeTrue();
    });

    it('continues draining stderr while waiting for process shutdown', function () {
        $manager = new ProcessManager(
            cliPath: PHP_BINARY,
            cliArgs: [
                '-r',
                'while (fread(STDIN, 1) !== "") {} fwrite(STDERR, str_repeat("x", 1000000)."shutdown diagnostic");',
                '--',
            ],
        );

        $manager->start();
        $manager->stop();
        $stderrOutput = $manager->getStderrOutput();

        expect(strlen($stderrOutput) <= 8192)->toBeTrue()
            ->and(str_ends_with($stderrOutput, 'shutdown diagnostic'))->toBeTrue();
    });
});

describe('ProcessManager Session Idle Timeout', function () {
    it('defaults sessionIdleTimeoutSeconds to 0', function () {
        $manager = new ProcessManager(cliPath: '/test/copilot');

        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('sessionIdleTimeoutSeconds');

        expect($property->getValue($manager))->toBe(0);
    });

    describe('ProcessManager mode environment', function () {
        it('disables keytar without enabling file logging in empty mode', function () {
            $manager = new ProcessManager(
                cliPath: '/test/copilot',
                mode: CopilotClientMode::Empty,
            );
            $method = (new ReflectionClass($manager))->getMethod('buildEnvironment');

            $environment = $method->invoke($manager, []);

            expect($environment['COPILOT_DISABLE_KEYTAR'])->toBe('1')
                ->and($environment)->not->toHaveKey('COPILOT_RUNTIME_PROCESS_FILE_LOGGING');
        });

        it('keeps default mode process file logging enabled', function () {
            $manager = new ProcessManager(cliPath: '/test/copilot');
            $method = (new ReflectionClass($manager))->getMethod('buildEnvironment');

            $environment = $method->invoke($manager, []);

            expect($environment['COPILOT_RUNTIME_PROCESS_FILE_LOGGING'])->toBe('1')
                ->and($environment)->not->toHaveKey('COPILOT_DISABLE_KEYTAR');
        });
    });

    it('accepts a positive sessionIdleTimeoutSeconds', function () {
        $manager = new ProcessManager(
            cliPath: '/test/copilot',
            sessionIdleTimeoutSeconds: 600,
        );

        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('sessionIdleTimeoutSeconds');

        expect($property->getValue($manager))->toBe(600);
    });
});
