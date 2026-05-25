<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\MCPAppsDiagnoseCapability;
use Revolution\Copilot\Types\Rpc\MCPAppsDiagnoseRequest;
use Revolution\Copilot\Types\Rpc\MCPAppsDiagnoseServer;
use Revolution\Copilot\Types\Rpc\MCPAppsListToolsRequest;

describe('MCPAppsDiagnoseCapability', function () {
    it('can be created from array', function () {
        $capability = MCPAppsDiagnoseCapability::fromArray([
            'advertised' => true,
            'featureFlagEnabled' => false,
            'sessionHasMcpApps' => true,
        ]);

        expect($capability->advertised)->toBeTrue()
            ->and($capability->featureFlagEnabled)->toBeFalse()
            ->and($capability->sessionHasMcpApps)->toBeTrue();
    });

    it('converts to array correctly', function () {
        $capability = new MCPAppsDiagnoseCapability(
            advertised: true,
            featureFlagEnabled: true,
            sessionHasMcpApps: false
        );

        expect($capability->toArray())->toBe([
            'advertised' => true,
            'featureFlagEnabled' => true,
            'sessionHasMcpApps' => false,
        ]);
    });
});

describe('MCPAppsDiagnoseRequest', function () {
    it('can be created from array', function () {
        $request = MCPAppsDiagnoseRequest::fromArray(['serverName' => 'test-server']);

        expect($request->serverName)->toBe('test-server');
    });

    it('converts to array correctly', function () {
        $request = new MCPAppsDiagnoseRequest(serverName: 'my-server');

        expect($request->toArray())->toBe(['serverName' => 'my-server']);
    });
});

describe('MCPAppsDiagnoseServer', function () {
    it('can be created from array', function () {
        $server = MCPAppsDiagnoseServer::fromArray([
            'connected' => true,
            'sampleToolNames' => ['tool1', 'tool2'],
            'toolCount' => 10.0,
            'toolsWithUiMeta' => 5.0,
        ]);

        expect($server->connected)->toBeTrue()
            ->and($server->sampleToolNames)->toBe(['tool1', 'tool2'])
            ->and($server->toolCount)->toBe(10.0)
            ->and($server->toolsWithUiMeta)->toBe(5.0);
    });
});

describe('MCPAppsListToolsRequest', function () {
    it('can be created from array', function () {
        $request = MCPAppsListToolsRequest::fromArray([
            'originServerName' => 'origin-server',
            'serverName' => 'target-server',
        ]);

        expect($request->originServerName)->toBe('origin-server')
            ->and($request->serverName)->toBe('target-server');
    });

    it('converts to array correctly', function () {
        $request = new MCPAppsListToolsRequest(
            originServerName: 'origin',
            serverName: 'target'
        );

        expect($request->toArray())->toBe([
            'originServerName' => 'origin',
            'serverName' => 'target',
        ]);
    });
});
