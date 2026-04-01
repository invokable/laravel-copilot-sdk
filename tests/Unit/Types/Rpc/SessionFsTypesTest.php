<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionFsSetProviderParams;
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderResult;

describe('SessionFsSetProviderParams', function () {
    it('can be created with all fields', function () {
        $params = new SessionFsSetProviderParams(
            initialCwd: '/home/user/project',
            sessionStatePath: '.copilot/sessions',
            conventions: 'posix',
        );

        expect($params->initialCwd)->toBe('/home/user/project')
            ->and($params->sessionStatePath)->toBe('.copilot/sessions')
            ->and($params->conventions)->toBe('posix');
    });

    it('defaults conventions to posix', function () {
        $params = new SessionFsSetProviderParams(
            initialCwd: '/app',
            sessionStatePath: '.state',
        );

        expect($params->conventions)->toBe('posix');
    });

    it('can be created from array', function () {
        $params = SessionFsSetProviderParams::fromArray([
            'initialCwd' => 'C:\\Users\\project',
            'sessionStatePath' => '.copilot\\sessions',
            'conventions' => 'windows',
        ]);

        expect($params->initialCwd)->toBe('C:\\Users\\project')
            ->and($params->sessionStatePath)->toBe('.copilot\\sessions')
            ->and($params->conventions)->toBe('windows');
    });

    it('converts to array', function () {
        $params = new SessionFsSetProviderParams(
            initialCwd: '/app',
            sessionStatePath: '.state',
            conventions: 'posix',
        );

        expect($params->toArray())->toBe([
            'initialCwd' => '/app',
            'sessionStatePath' => '.state',
            'conventions' => 'posix',
        ]);
    });
});

describe('SessionFsSetProviderResult', function () {
    it('can be created with success true', function () {
        $result = new SessionFsSetProviderResult(success: true);

        expect($result->success)->toBeTrue();
    });

    it('can be created from array', function () {
        $result = SessionFsSetProviderResult::fromArray(['success' => true]);

        expect($result->success)->toBeTrue();
    });

    it('defaults success to false', function () {
        $result = SessionFsSetProviderResult::fromArray([]);

        expect($result->success)->toBeFalse();
    });

    it('converts to array', function () {
        $result = new SessionFsSetProviderResult(success: true);

        expect($result->toArray())->toBe(['success' => true]);
    });
});
