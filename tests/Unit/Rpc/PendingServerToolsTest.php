<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerTools;
use Revolution\Copilot\Types\Rpc\ToolsListParams;
use Revolution\Copilot\Types\Rpc\ToolsListResult;

describe('PendingServerTools', function () {
    it('calls tools.list with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'tools.list',
                Mockery::on(fn ($params) => $params['model'] === 'claude-sonnet-4.5'),
            )
            ->andReturn([
                'tools' => [
                    ['name' => 'read_file', 'description' => 'Read a file'],
                    ['name' => 'write_file', 'description' => 'Write a file'],
                ],
            ]);

        $pending = new PendingServerTools($client);
        $result = $pending->list(new ToolsListParams(model: 'claude-sonnet-4.5'));

        expect($result)->toBeInstanceOf(ToolsListResult::class)
            ->and($result->tools)->toHaveCount(2)
            ->and($result->tools[0]['name'])->toBe('read_file');
    });

    it('calls tools.list with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'tools.list',
                Mockery::on(fn ($params) => $params['model'] === 'gpt-5'),
            )
            ->andReturn(['tools' => []]);

        $pending = new PendingServerTools($client);
        $result = $pending->list(['model' => 'gpt-5']);

        expect($result)->toBeInstanceOf(ToolsListResult::class)
            ->and($result->tools)->toBeEmpty();
    });

    it('calls tools.list with no params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'tools.list',
                Mockery::on(fn ($params) => ! isset($params['model'])),
            )
            ->andReturn([
                'tools' => [['name' => 'bash', 'description' => 'Run bash commands']],
            ]);

        $pending = new PendingServerTools($client);
        $result = $pending->list();

        expect($result)->toBeInstanceOf(ToolsListResult::class)
            ->and($result->tools)->toHaveCount(1);
    });
});
