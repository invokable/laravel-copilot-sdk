<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\CommandContext;

describe('CommandContext', function () {
    it('can be created with constructor', function () {
        $ctx = new CommandContext(
            sessionId: 'session-123',
            command: '/deploy production',
            commandName: 'deploy',
            args: 'production',
        );

        expect($ctx->sessionId)->toBe('session-123')
            ->and($ctx->command)->toBe('/deploy production')
            ->and($ctx->commandName)->toBe('deploy')
            ->and($ctx->args)->toBe('production');
    });

    it('can be created from array', function () {
        $ctx = CommandContext::fromArray([
            'sessionId' => 'session-456',
            'command' => '/test --verbose',
            'commandName' => 'test',
            'args' => '--verbose',
        ]);

        expect($ctx->sessionId)->toBe('session-456')
            ->and($ctx->command)->toBe('/test --verbose')
            ->and($ctx->commandName)->toBe('test')
            ->and($ctx->args)->toBe('--verbose');
    });

    it('can convert to array', function () {
        $ctx = new CommandContext(
            sessionId: 'session-789',
            command: '/build',
            commandName: 'build',
            args: '',
        );

        expect($ctx->toArray())->toBe([
            'sessionId' => 'session-789',
            'command' => '/build',
            'commandName' => 'build',
            'args' => '',
        ]);
    });

    it('implements Arrayable interface', function () {
        $ctx = new CommandContext(
            sessionId: 's',
            command: '/c',
            commandName: 'c',
            args: '',
        );

        expect($ctx)->toBeInstanceOf(Arrayable::class);
    });

    it('roundtrips through fromArray and toArray', function () {
        $data = [
            'sessionId' => 'session-roundtrip',
            'command' => '/deploy staging --force',
            'commandName' => 'deploy',
            'args' => 'staging --force',
        ];

        $ctx = CommandContext::fromArray($data);

        expect($ctx->toArray())->toBe($data);
    });
});
