<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerSkillsInstallations;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\SkillInstallationOperationRequest;
use Revolution\Copilot\Types\Rpc\SkillInstallationsRequest;
use Revolution\Copilot\Types\Rpc\SkillSetEnabledRequest;

describe('PendingServerSkillsInstallations', function () {
    it('lists, recovers, checks, cancels, and enables skill installations', function () {
        $contractData = ['protocolVersion' => 3, 'requiredCapabilities' => ['ai-skill-installation']];
        $contract = new CatalogClientContract(3, ['ai-skill-installation']);
        $cases = [
            [
                'list',
                'skills.installations.list',
                ['contract' => $contractData, 'policySessionId' => 'session-1'],
                new SkillInstallationsRequest($contract, 'session-1'),
                ['kind' => 'negotiation-refused'],
            ],
            [
                'recover',
                'skills.installations.recover',
                ['contract' => $contractData, 'policySessionId' => 'session-1'],
                new SkillInstallationsRequest($contract, 'session-1'),
                ['kind' => 'invalid-request'],
            ],
            [
                'status',
                'skills.installations.status',
                ['contract' => $contractData, 'operationId' => 'operation-1'],
                new SkillInstallationOperationRequest($contract, 'operation-1'),
                ['kind' => 'status'],
            ],
            [
                'cancel',
                'skills.installations.cancel',
                ['contract' => $contractData, 'operationId' => 'operation-1'],
                new SkillInstallationOperationRequest($contract, 'operation-1'),
                ['kind' => 'cancelled'],
            ],
            [
                'setEnabled',
                'skills.installations.setEnabled',
                ['contract' => $contractData, 'installationId' => 'installation-1', 'enabled' => true, 'policySessionId' => 'session-1'],
                new SkillSetEnabledRequest($contract, 'installation-1', true, 'session-1'),
                ['kind' => 'outcome'],
            ],
        ];
        $client = Mockery::mock(JsonRpcClient::class);

        foreach ($cases as [$method, $rpcMethod, $params, $typedParams, $result]) {
            $client->shouldReceive('request')
                ->twice()
                ->with($rpcMethod, $params)
                ->andReturn($result, $result);
        }

        $installations = (new ServerRpc($client))->skills()->installations();

        expect($installations)->toBeInstanceOf(PendingServerSkillsInstallations::class);

        foreach ($cases as [$method, $rpcMethod, $params, $typedParams, $expectedResult]) {
            expect($installations->{$method}($params))->toBe($expectedResult)
                ->and($installations->{$method}($typedParams))->toBe($expectedResult);
        }
    });
});
