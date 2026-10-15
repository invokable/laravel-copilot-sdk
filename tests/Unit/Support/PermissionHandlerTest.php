<?php

declare(strict_types=1);

use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Support\PermissionRequestResultKind;

describe('PermissionHandler', function () {
    it('approveAll returns a closure', function () {
        $handler = PermissionHandler::approveAll();

        expect($handler)->toBeInstanceOf(Closure::class);
    });

    it('approveAll closure returns approve-once kind', function () {
        $handler = PermissionHandler::approveAll();
        $result = $handler(['kind' => 'shell'], ['sessionId' => 'test-session']);

        expect($result)->toBe(['kind' => 'approve-once']);
    });

    it('approveAll closure throws when managed settings are enabled', function () {
        $handler = PermissionHandler::approveAll();

        expect(fn () => $handler(['kind' => 'shell'], [
            'sessionId' => 'test-session',
            'managedSettingsEnabled' => true,
        ]))->toThrow(RuntimeException::class, 'approveAll cannot be used when managed settings are enabled.');
    });

    it('approveAll closure returns no-result when managed approval is required', function () {
        $handler = PermissionHandler::approveAll();
        $result = $handler(
            ['kind' => 'shell', 'managedApprovalRequired' => true],
            ['sessionId' => 'test-session', 'managedSettingsEnabled' => false],
        );

        expect($result)->toBe(['kind' => PermissionRequestResultKind::NO_RESULT]);
    });

    it('uses the managed approval requirement from supported prompt contexts', function (array $request, array $promptRequest, array $expected) {
        $handler = PermissionHandler::approveAll();
        $result = $handler($request, [
            'sessionId' => 'test-session',
            'managedSettingsEnabled' => false,
            'promptRequest' => $promptRequest,
        ]);

        expect($result)->toBe($expected);
    })->with([
        'explicitly not requiring a human response' => [
            ['kind' => 'read', 'managedApprovalRequired' => true],
            ['kind' => 'read', 'managedApprovalRequired' => false],
            ['kind' => 'approve-once'],
        ],
        'requiring a human response' => [
            ['kind' => 'read', 'managedApprovalRequired' => false],
            ['kind' => 'read', 'managedApprovalRequired' => true],
            ['kind' => PermissionRequestResultKind::NO_RESULT],
        ],
        'commands prompt' => [
            ['kind' => 'shell', 'managedApprovalRequired' => true],
            ['kind' => 'commands', 'managedApprovalRequired' => false],
            ['kind' => 'approve-once'],
        ],
        'write prompt' => [
            ['kind' => 'write', 'managedApprovalRequired' => true],
            ['kind' => 'write', 'managedApprovalRequired' => false],
            ['kind' => 'approve-once'],
        ],
        'url prompt' => [
            ['kind' => 'url', 'managedApprovalRequired' => true],
            ['kind' => 'url', 'managedApprovalRequired' => false],
            ['kind' => 'approve-once'],
        ],
        'workflow prompt' => [
            ['kind' => 'workflow', 'managedApprovalRequired' => true],
            ['kind' => 'workflow', 'managedApprovalRequired' => false],
            ['kind' => 'approve-once'],
        ],
    ]);

    it('falls back to the raw marker for unsupported or incomplete prompt contexts', function (array $request, mixed $promptRequest, array $expected) {
        $handler = PermissionHandler::approveAll();
        $result = $handler($request, [
            'sessionId' => 'test-session',
            'managedSettingsEnabled' => false,
            'promptRequest' => $promptRequest,
        ]);

        expect($result)->toBe($expected);
    })->with([
        'unsupported kind with managed approval required' => [
            ['kind' => 'memory', 'managedApprovalRequired' => true],
            ['kind' => 'memory', 'managedApprovalRequired' => false],
            ['kind' => PermissionRequestResultKind::NO_RESULT],
        ],
        'unsupported kind without managed approval required' => [
            ['kind' => 'memory', 'managedApprovalRequired' => false],
            ['kind' => 'memory', 'managedApprovalRequired' => true],
            ['kind' => 'approve-once'],
        ],
        'missing prompt marker' => [
            ['kind' => 'read', 'managedApprovalRequired' => true],
            ['kind' => 'read'],
            ['kind' => PermissionRequestResultKind::NO_RESULT],
        ],
        'missing prompt kind' => [
            ['kind' => 'read', 'managedApprovalRequired' => true],
            ['managedApprovalRequired' => false],
            ['kind' => PermissionRequestResultKind::NO_RESULT],
        ],
        'malformed prompt context' => [
            ['kind' => 'read', 'managedApprovalRequired' => false],
            'unknown',
            ['kind' => 'approve-once'],
        ],
    ]);

    it('approveSafety returns a closure', function () {
        $handler = PermissionHandler::approveSafety();

        expect($handler)->toBeInstanceOf(Closure::class);
    });

    it('approveSafety closure returns reject kind for shell', function () {
        $handler = PermissionHandler::approveSafety();
        $result = $handler(['kind' => 'shell'], ['sessionId' => 'test-session']);

        expect($result)->toBe(['kind' => PermissionRequestResultKind::REJECT]);
    });

    it('approveSafety closure returns approve-once kind for read', function () {
        $handler = PermissionHandler::approveSafety();
        $result = $handler(['kind' => 'read'], ['sessionId' => 'test-session']);

        expect($result)->toBe(['kind' => 'approve-once']);
    });

    it('denyAll', function () {
        $handler = PermissionHandler::denyAll();
        $result = $handler(['kind' => 'read'], ['sessionId' => 'test-session']);

        expect($result)->toBe(['kind' => PermissionRequestResultKind::REJECT]);
    });
});
