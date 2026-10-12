<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\McpInstallationSecretStorage;
use Revolution\Copilot\Enums\McpPlanValueCategory;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\McpApplyInstallRequest;
use Revolution\Copilot\Types\Rpc\McpApplyUninstallRequest;
use Revolution\Copilot\Types\Rpc\McpInstallationInput;
use Revolution\Copilot\Types\Rpc\McpInstallationOperationRequest;
use Revolution\Copilot\Types\Rpc\McpInstallationSecret;
use Revolution\Copilot\Types\Rpc\McpInstallationsRequest;
use Revolution\Copilot\Types\Rpc\McpPlanUninstallRequest;
use Revolution\Copilot\Types\Rpc\McpPrepareInstallRequest;
use Revolution\Copilot\Types\Rpc\McpServerCardEmbedded;
use Revolution\Copilot\Types\Rpc\McpServerCardReference;

describe('McpPrepareInstallRequest', function () {
    it('round-trips nested values and source using the camelCase wire format', function () {
        $request = McpPrepareInstallRequest::fromArray([
            'contract' => ['protocolVersion' => 1, 'requiredCapabilities' => ['mcp-server-card']],
            'planHandle' => 'plan-1',
            'choiceId' => 'choice-1',
            'policySessionId' => 'session-1',
            'inputs' => [
                ['category' => 'header', 'key' => 'Authorization', 'value' => '******'],
            ],
            'secrets' => [
                ['placeholder' => '$API_KEY', 'value' => 'secret-value'],
            ],
            'secretStorage' => 'private-file',
            'source' => [
                'kind' => 'embedded',
                'mediaType' => 'application/mcp-server-card+json',
                'data' => '{"name":"example"}',
            ],
        ]);

        expect($request->inputs[0])->toBeInstanceOf(McpInstallationInput::class)
            ->and($request->inputs[0]->category)->toBe(McpPlanValueCategory::Header)
            ->and($request->secrets[0])->toBeInstanceOf(McpInstallationSecret::class)
            ->and($request->source)->toBeInstanceOf(McpServerCardReference::class)
            ->and($request->source->card)->toBeInstanceOf(McpServerCardEmbedded::class)
            ->and($request->secretStorage)->toBe(McpInstallationSecretStorage::PrivateFile)
            ->and($request->toArray())->toBe([
                'contract' => ['protocolVersion' => 1, 'requiredCapabilities' => ['mcp-server-card']],
                'planHandle' => 'plan-1',
                'choiceId' => 'choice-1',
                'policySessionId' => 'session-1',
                'inputs' => [
                    ['category' => 'header', 'key' => 'Authorization', 'value' => '******'],
                ],
                'secrets' => [
                    ['placeholder' => '$API_KEY', 'value' => 'secret-value'],
                ],
                'secretStorage' => 'private-file',
                'source' => [
                    'kind' => 'embedded',
                    'mediaType' => 'application/mcp-server-card+json',
                    'data' => '{"name":"example"}',
                ],
            ]);
    });
});

describe('MCP installation lifecycle requests', function () {
    it('serializes required session context without adding it to operation requests', function () {
        $contract = new CatalogClientContract(1, []);

        expect((new McpApplyInstallRequest($contract, 'operation-1', 'session-1'))->toArray())
            ->toBe(['contract' => ['protocolVersion' => 1, 'requiredCapabilities' => []], 'operationId' => 'operation-1', 'policySessionId' => 'session-1'])
            ->and((new McpPlanUninstallRequest($contract, 'installation-1', 'session-1'))->toArray())
            ->toBe(['contract' => ['protocolVersion' => 1, 'requiredCapabilities' => []], 'installationId' => 'installation-1', 'policySessionId' => 'session-1'])
            ->and((new McpApplyUninstallRequest($contract, 'plan-1', 'session-1'))->toArray())
            ->toBe(['contract' => ['protocolVersion' => 1, 'requiredCapabilities' => []], 'planHandle' => 'plan-1', 'policySessionId' => 'session-1'])
            ->and((new McpInstallationsRequest($contract, 'session-1'))->toArray())
            ->toBe(['contract' => ['protocolVersion' => 1, 'requiredCapabilities' => []], 'policySessionId' => 'session-1'])
            ->and((new McpInstallationOperationRequest($contract, 'operation-1'))->toArray())
            ->toBe(['contract' => ['protocolVersion' => 1, 'requiredCapabilities' => []], 'operationId' => 'operation-1']);
    });
});
