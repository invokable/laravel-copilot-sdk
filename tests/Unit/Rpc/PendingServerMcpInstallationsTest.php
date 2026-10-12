<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerMcpConfig;
use Revolution\Copilot\Rpc\PendingServerMcpInstallations;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\McpInstallationOperationRequest;
use Revolution\Copilot\Types\Rpc\McpInstallationsRequest;

describe('PendingServerMcpInstallations', function () {
    it('is available from the server MCP RPC group', function () {
        $client = Mockery::mock(JsonRpcClient::class);

        expect((new PendingServerMcpConfig($client))->installations())
            ->toBeInstanceOf(PendingServerMcpInstallations::class);
    });

    it('lists and recovers installations with a typed contract', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $params = [
            'contract' => ['protocolVersion' => 1, 'requiredCapabilities' => ['mcp-server-card']],
            'policySessionId' => 'session-1',
        ];
        $client->shouldReceive('request')
            ->twice()
            ->with('mcp.installations.list', $params)
            ->andReturn(['kind' => 'listed']);
        $client->shouldReceive('request')
            ->twice()
            ->with('mcp.installations.recover', $params)
            ->andReturn(['kind' => 'recovered']);

        $installations = (new PendingServerMcpConfig($client))->installations();
        $list = $installations->list(new McpInstallationsRequest(
            contract: new CatalogClientContract(1, ['mcp-server-card']),
            policySessionId: 'session-1',
        ));
        $listFromArray = $installations->list($params);
        $recover = $installations->recover([
            'contract' => ['protocolVersion' => 1, 'requiredCapabilities' => ['mcp-server-card']],
            'policySessionId' => 'session-1',
        ]);
        $recoverFromRequest = $installations->recover(new McpInstallationsRequest(
            contract: new CatalogClientContract(1, ['mcp-server-card']),
            policySessionId: 'session-1',
        ));

        expect($list)->toBe(['kind' => 'listed'])
            ->and($listFromArray)->toBe(['kind' => 'listed'])
            ->and($recover)->toBe(['kind' => 'recovered']);
        expect($recoverFromRequest)->toBe(['kind' => 'recovered']);
    });

    it('checks and cancels an operation using typed and array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $params = [
            'contract' => ['protocolVersion' => 1, 'requiredCapabilities' => []],
            'operationId' => 'operation-1',
        ];
        $client->shouldReceive('request')
            ->twice()
            ->with('mcp.installations.status', $params)
            ->andReturn(['kind' => 'status']);
        $client->shouldReceive('request')
            ->twice()
            ->with('mcp.installations.cancel', $params)
            ->andReturn(['kind' => 'cancelled']);

        $installations = (new PendingServerMcpConfig($client))->installations();
        $status = $installations->status(new McpInstallationOperationRequest(
            contract: new CatalogClientContract(1, []),
            operationId: 'operation-1',
        ));
        $statusFromArray = $installations->status($params);
        $cancel = $installations->cancel([
            'contract' => ['protocolVersion' => 1, 'requiredCapabilities' => []],
            'operationId' => 'operation-1',
        ]);
        $cancelFromRequest = $installations->cancel(new McpInstallationOperationRequest(
            contract: new CatalogClientContract(1, []),
            operationId: 'operation-1',
        ));

        expect($status)->toBe(['kind' => 'status'])
            ->and($statusFromArray)->toBe(['kind' => 'status'])
            ->and($cancel)->toBe(['kind' => 'cancelled']);
        expect($cancelFromRequest)->toBe(['kind' => 'cancelled']);
    });
});
