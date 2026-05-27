<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\RuntimeConnectionKind;
use Revolution\Copilot\Types\RuntimeConnection;

describe('RuntimeConnection', function () {
    it('creates stdio connections', function () {
        $connection = RuntimeConnection::forStdio('/usr/local/bin/copilot', ['--debug']);

        expect($connection->kind)->toBe(RuntimeConnectionKind::STDIO)
            ->and($connection->toArray())->toBe([
                'kind' => 'stdio',
                'path' => '/usr/local/bin/copilot',
                'args' => ['--debug'],
            ]);
    });

    it('creates uri connections', function () {
        $connection = RuntimeConnection::forUri('tcp://127.0.0.1:12345', 'secret');

        expect($connection->kindValue())->toBe('uri')
            ->and($connection->toArray())->toBe([
                'kind' => 'uri',
                'url' => 'tcp://127.0.0.1:12345',
                'connectionToken' => 'secret',
            ]);
    });

    it('creates tcp connection shapes for future sdk-spawned tcp support', function () {
        $connection = RuntimeConnection::forTcp(port: 9001, connectionToken: 'secret', path: 'copilot', args: ['--headless']);

        expect($connection->toArray())->toBe([
            'kind' => 'tcp',
            'path' => 'copilot',
            'args' => ['--headless'],
            'port' => 9001,
            'connectionToken' => 'secret',
        ]);
    });

    it('can be created from array', function () {
        $connection = RuntimeConnection::fromArray([
            'kind' => 'uri',
            'url' => '127.0.0.1:12345',
        ]);

        expect($connection)->toBeInstanceOf(Arrayable::class)
            ->and($connection->kindValue())->toBe('uri')
            ->and($connection->url)->toBe('127.0.0.1:12345');
    });
});
