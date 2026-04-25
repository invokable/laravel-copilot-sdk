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
