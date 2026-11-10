<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerLsp;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\LspListRequest;
use Revolution\Copilot\Types\Rpc\LspListResult;

describe('PendingServerLsp', function () {
    it('lists process-wide language-server configurations from typed and array parameters', function () {
        $response = [
            'configs' => [[
                'id' => 'php',
                'fileExtensions' => ['php' => 'php'],
                'disabled' => false,
            ]],
            'userServers' => [],
            'pluginServers' => [],
            'projectServers' => [],
            'userConfigPath' => '/home/user/.config/copilot/lsp.json',
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('lsp.list', ['workingDirectory' => '/workspace'])
            ->andReturn($response, $response);

        $lsp = (new ServerRpc($client))->lsp();
        $typedResult = $lsp->list(new LspListRequest('/workspace'));
        $arrayResult = $lsp->list(['workingDirectory' => '/workspace']);

        expect($lsp)->toBeInstanceOf(PendingServerLsp::class)
            ->and($typedResult)->toBeInstanceOf(LspListResult::class)
            ->and($typedResult->configs[0]->id)->toBe('php')
            ->and($arrayResult->userConfigPath)->toBe('/home/user/.config/copilot/lsp.json');
    });
});
