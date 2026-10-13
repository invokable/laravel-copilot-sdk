<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerCommands;
use Revolution\Copilot\Types\Rpc\CommandList;

describe('PendingServerCommands', function () {
    it('lists built-in slash commands without session parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('commands.list', [])
            ->andReturn(['commands' => []]);

        $result = (new PendingServerCommands($client))->list();

        expect($result)->toBeInstanceOf(CommandList::class)
            ->and($result->commands)->toBeEmpty();
    });
});
