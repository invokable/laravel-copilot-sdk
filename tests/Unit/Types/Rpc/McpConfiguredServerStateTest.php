<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\McpServerStatus;
use Revolution\Copilot\Types\Rpc\McpConfiguredServerState;

describe('McpConfiguredServerState', function () {
    it('preserves the optional tools loaded state', function () {
        $state = McpConfiguredServerState::fromArray([
            'status' => 'connected',
            'error' => 'previous error',
            'toolsLoaded' => true,
        ]);

        expect($state->status)->toBe(McpServerStatus::CONNECTED)
            ->and($state->error)->toBe('previous error')
            ->and($state->toolsLoaded)->toBeTrue()
            ->and($state->toArray())->toBe([
                'status' => 'connected',
                'error' => 'previous error',
                'toolsLoaded' => true,
            ]);
    });

    it('distinguishes tools not loaded from a state where loading is not reported', function () {
        $notLoaded = McpConfiguredServerState::fromArray([
            'status' => 'connected',
            'toolsLoaded' => false,
        ]);
        $notReported = McpConfiguredServerState::fromArray([
            'status' => 'pending',
        ]);

        expect($notLoaded->toolsLoaded)->toBeFalse()
            ->and($notLoaded->toArray())->toBe([
                'status' => 'connected',
                'toolsLoaded' => false,
            ])
            ->and($notReported->toolsLoaded)->toBeNull()
            ->and($notReported->toArray())->toBe(['status' => 'pending']);
    });
});
