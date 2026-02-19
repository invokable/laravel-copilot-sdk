<?php

declare(strict_types=1);

use Revolution\Copilot\Support\PermissionHandler;

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
});
