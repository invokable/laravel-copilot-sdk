<?php

declare(strict_types=1);

use Revolution\Copilot\Process\ProcessManager;

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
