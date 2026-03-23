<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Session;
use Revolution\Copilot\Types\CommandContext;
use Revolution\Copilot\Types\CommandDefinition;

describe('HasCommandHandlers', function () {
    it('can register and retrieve command handlers', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $handler = fn (CommandContext $ctx) => null;

        $session->registerCommands([
            ['name' => 'deploy', 'handler' => $handler],
        ]);

        expect($session->getCommandHandler('deploy'))->toBe($handler);
    });

    it('returns null for unregistered command', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        expect($session->getCommandHandler('nonexistent'))->toBeNull();
    });

    it('can register multiple commands', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $deployHandler = fn () => null;
        $testHandler = fn () => null;

        $session->registerCommands([
            ['name' => 'deploy', 'handler' => $deployHandler],
            ['name' => 'test', 'handler' => $testHandler],
        ]);

        expect($session->getCommandHandler('deploy'))->toBe($deployHandler)
            ->and($session->getCommandHandler('test'))->toBe($testHandler);
    });

    it('replaces all handlers on re-registration', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $originalHandler = fn () => null;
        $newHandler = fn () => null;

        $session->registerCommands([
            ['name' => 'deploy', 'handler' => $originalHandler],
            ['name' => 'test', 'handler' => fn () => null],
        ]);

        $session->registerCommands([
            ['name' => 'deploy', 'handler' => $newHandler],
        ]);

        expect($session->getCommandHandler('deploy'))->toBe($newHandler)
            ->and($session->getCommandHandler('test'))->toBeNull();
    });

    it('skips entries without name or handler', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $session->registerCommands([
            ['name' => 'valid', 'handler' => fn () => null],
            ['name' => 'missing-handler'],
            ['handler' => fn () => null],
            [],
        ]);

        expect($session->getCommandHandler('valid'))->not->toBeNull()
            ->and($session->getCommandHandler('missing-handler'))->toBeNull();
    });

    it('accepts CommandDefinition objects via toArray', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $def = new CommandDefinition(
            name: 'deploy',
            handler: fn () => null,
            description: 'Deploy the app',
        );

        $session->registerCommands([$def->toArray()]);

        expect($session->getCommandHandler('deploy'))->not->toBeNull();
    });

    it('accepts CommandDefinition objects directly', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $handler = fn (CommandContext $ctx) => null;
        $def = new CommandDefinition(
            name: 'deploy',
            handler: $handler,
            description: 'Deploy the app',
        );

        $session->registerCommands([$def]);

        expect($session->getCommandHandler('deploy'))->toBe($handler);
    });
});
