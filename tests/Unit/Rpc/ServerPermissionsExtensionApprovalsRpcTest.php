<?php

declare(strict_types=1);

use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApproval;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsGrantParams;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsGrantResult;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsListParams;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsListResult;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsRevokeParams;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsRevokeResult;

describe('server extension approval RPCs', function () {
    it('sends grant requests with a required session and returns the approval', function () {
        $approval = [
            'extensionName' => 'example',
            'pluginName' => 'plugin',
            'capabilities' => ['read', 'write'],
            'grantedAt' => '2026-01-01T00:00:00Z',
            'installationKey' => 'install-1',
            'installationLabel' => 'Example',
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->twice()
            ->with('permissions.extensionApprovals.grant', ['sessionId' => 'session-1', 'requestId' => 'request-1'])
            ->andReturn(['granted' => true, 'approval' => $approval]);

        $approvals = (new ServerRpc($client))->permissions()->extensionApprovals();
        $typed = $approvals->grant(new PermissionsExtensionApprovalsGrantParams(sessionId: 'session-1', requestId: 'request-1'));
        $array = $approvals->grant(['sessionId' => 'session-1', 'requestId' => 'request-1']);

        expect($typed)->toBeInstanceOf(PermissionsExtensionApprovalsGrantResult::class)
            ->and($typed->granted)->toBeTrue()
            ->and($typed->approval)->toBeInstanceOf(PermissionsExtensionApproval::class)
            ->and($typed->approval->toArray())->toBe($approval)
            ->and($array->toArray())->toBe(['granted' => true, 'approval' => $approval]);
    });

    it('lists approvals with and without a session filter using typed and array parameters', function () {
        $approval = [
            'extensionName' => 'example',
            'pluginName' => 'plugin',
            'capabilities' => ['read'],
            'grantedAt' => '2026-01-01T00:00:00Z',
            'installationKey' => 'install-1',
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->twice()
            ->with('permissions.extensionApprovals.list', ['sessionId' => 'session-1'])
            ->andReturn(['approvals' => [$approval]]);
        $client->shouldReceive('request')->twice()
            ->with('permissions.extensionApprovals.list', [])
            ->andReturn(['approvals' => []]);

        $approvals = (new ServerRpc($client))->permissions()->extensionApprovals();
        $typed = $approvals->list(new PermissionsExtensionApprovalsListParams(sessionId: 'session-1'));
        $array = $approvals->list(['sessionId' => 'session-1']);
        $default = $approvals->list();
        $unfiltered = $approvals->list(['sessionId' => null]);

        expect($typed)->toBeInstanceOf(PermissionsExtensionApprovalsListResult::class)
            ->and($typed->approvals[0])->toBeInstanceOf(PermissionsExtensionApproval::class)
            ->and($typed->toArray())->toBe(['approvals' => [$approval]])
            ->and($array->toArray())->toBe(['approvals' => [$approval]])
            ->and($default->approvals)->toBe([])
            ->and($unfiltered->approvals)->toBe([]);
    });

    it('rejects a grant without a session before sending an RPC request', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldNotReceive('request');
        $approvals = (new ServerRpc($client))->permissions()->extensionApprovals();

        expect(fn () => $approvals->grant(['requestId' => 'request-1']))
            ->toThrow(InvalidArgumentException::class);
    });

    it('revokes approvals with optional session filters using typed and array parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->twice()
            ->with('permissions.extensionApprovals.revoke', [
                'extensionName' => 'example',
                'installationKey' => 'install-1',
                'sessionId' => 'session-1',
            ])
            ->andReturn(['revoked' => true]);
        $client->shouldReceive('request')->twice()
            ->with('permissions.extensionApprovals.revoke', ['extensionName' => 'example', 'installationKey' => 'install-1'])
            ->andReturn(['revoked' => false]);

        $approvals = (new ServerRpc($client))->permissions()->extensionApprovals();
        $typed = $approvals->revoke(new PermissionsExtensionApprovalsRevokeParams('example', 'install-1', 'session-1'));
        $array = $approvals->revoke(['extensionName' => 'example', 'installationKey' => 'install-1', 'sessionId' => 'session-1']);
        $typedUnfiltered = $approvals->revoke(new PermissionsExtensionApprovalsRevokeParams('example', 'install-1'));
        $arrayUnfiltered = $approvals->revoke(['extensionName' => 'example', 'installationKey' => 'install-1', 'sessionId' => null]);

        expect($typed)->toBeInstanceOf(PermissionsExtensionApprovalsRevokeResult::class)
            ->and($typed->revoked)->toBeTrue()
            ->and($array->revoked)->toBeTrue()
            ->and($typedUnfiltered->revoked)->toBeFalse()
            ->and($arrayUnfiltered->revoked)->toBeFalse();
    });

    it('propagates JSON-RPC errors from each approval operation', function (string $method, array $params, string $wireMethod) {
        $exception = new JsonRpcException(-32000, 'Approval failed', ['reason' => 'denied']);
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()->with($wireMethod, $params)->andThrow($exception);
        $approvals = (new ServerRpc($client))->permissions()->extensionApprovals();

        try {
            $approvals->{$method}($params);
            test()->fail('Expected the original JSON-RPC exception.');
        } catch (JsonRpcException $caught) {
            expect($caught)->toBe($exception);
        }
    })->with([
        'grant' => ['grant', ['sessionId' => 'session-1', 'requestId' => 'request-1'], 'permissions.extensionApprovals.grant'],
        'list' => ['list', [], 'permissions.extensionApprovals.list'],
        'revoke' => ['revoke', ['extensionName' => 'example', 'installationKey' => 'install-1'], 'permissions.extensionApprovals.revoke'],
    ]);
});
