<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerSkillsInstallations;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\SkillInstallationManagementResult;
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
                ['kind' => 'network-failure', 'message' => 'Offline', 'reason' => 'offline'],
            ],
            [
                'recover',
                'skills.installations.recover',
                ['contract' => $contractData, 'policySessionId' => 'session-1'],
                new SkillInstallationsRequest($contract, 'session-1'),
                ['kind' => 'outcome', 'outcome' => ['kind' => 'listed', 'installations' => []]],
            ],
            [
                'status',
                'skills.installations.status',
                ['contract' => $contractData, 'operationId' => 'operation-1'],
                new SkillInstallationOperationRequest($contract, 'operation-1'),
                ['kind' => 'outcome', 'outcome' => ['kind' => 'operation', 'operation' => ['status' => 'running']]],
            ],
            [
                'cancel',
                'skills.installations.cancel',
                ['contract' => $contractData, 'operationId' => 'operation-1'],
                new SkillInstallationOperationRequest($contract, 'operation-1'),
                ['kind' => 'outcome', 'outcome' => ['kind' => 'rolled-back', 'operationId' => 'operation-1']],
            ],
            [
                'setEnabled',
                'skills.installations.setEnabled',
                ['contract' => $contractData, 'installationId' => 'installation-1', 'enabled' => true, 'policySessionId' => 'session-1'],
                new SkillSetEnabledRequest($contract, 'installation-1', true, 'session-1'),
                [
                    'kind' => 'outcome',
                    'outcome' => [
                        'kind' => 'refused',
                        'reason' => 'source-rate-limited',
                        'retryAfterSeconds' => 120,
                    ],
                ],
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
            $arrayResult = $installations->{$method}($params);
            $typedResult = $installations->{$method}($typedParams);

            expect($arrayResult)->toBeInstanceOf(SkillInstallationManagementResult::class)
                ->and($arrayResult->toArray())->toBe($expectedResult)
                ->and($typedResult)->toBeInstanceOf(SkillInstallationManagementResult::class)
                ->and($typedResult->toArray())->toBe($expectedResult);
        }
    });
});
