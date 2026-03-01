<?php

declare(strict_types=1);

use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Support\PermissionRequestKind;

describe('PermissionHandler', function () {
    it('approveAll returns a closure', function () {
        $handler = PermissionHandler::approveAll();

        expect($handler)->toBeInstanceOf(Closure::class);
    });

    it('approveAll closure returns approved kind', function () {
        $handler = PermissionHandler::approveAll();
        $result = $handler(['kind' => 'shell'], ['sessionId' => 'test-session']);

        expect($result)->toBe(['kind' => 'approved']);
    });

    it('approveSafety returns a closure', function () {
        $handler = PermissionHandler::approveSafety();

        expect($handler)->toBeInstanceOf(Closure::class);
    });

    it('approveSafety closure returns denied kind', function () {
        $handler = PermissionHandler::approveSafety();
        $result = $handler(['kind' => 'shell'], ['sessionId' => 'test-session']);

        expect($result)->toBe(['kind' => PermissionRequestKind::DENIED_INTERACTIVELY_BY_USER]);
    });

    it('approveSafety closure returns approved kind', function () {
        $handler = PermissionHandler::approveSafety();
        $result = $handler(['kind' => 'read'], ['sessionId' => 'test-session']);

        expect($result)->toBe(['kind' => 'approved']);
    });
});
