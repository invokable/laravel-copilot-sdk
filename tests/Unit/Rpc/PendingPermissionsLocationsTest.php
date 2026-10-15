<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\PermissionLocationType;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingPermissions;
use Revolution\Copilot\Rpc\PendingPermissionsFolderTrust;
use Revolution\Copilot\Rpc\PendingPermissionsLocations;
use Revolution\Copilot\Types\Rpc\FolderTrustAddParams;
use Revolution\Copilot\Types\Rpc\FolderTrustAddResult;
use Revolution\Copilot\Types\Rpc\FolderTrustCheckParams;
use Revolution\Copilot\Types\Rpc\FolderTrustCheckResult;
use Revolution\Copilot\Types\Rpc\PermissionLocationAddToolApprovalParams;
use Revolution\Copilot\Types\Rpc\PermissionLocationAddToolApprovalResult;
use Revolution\Copilot\Types\Rpc\PermissionLocationApplyParams;
use Revolution\Copilot\Types\Rpc\PermissionLocationApplyResult;
use Revolution\Copilot\Types\Rpc\PermissionLocationResolveParams;
use Revolution\Copilot\Types\Rpc\PermissionLocationResolveResult;
use Revolution\Copilot\Types\Rpc\PermissionsLocationsAddToolApprovalDetailsCommands;

it('exposes session locations and folder trust RPC groups', function () {
    $permissions = new PendingPermissions(Mockery::mock(JsonRpcClient::class), 'session-1');

    expect($permissions->locations())->toBeInstanceOf(PendingPermissionsLocations::class)
        ->and($permissions->folderTrust())->toBeInstanceOf(PendingPermissionsFolderTrust::class);
});

it('sends typed and array location and folder trust requests with session id', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->with('session.permissions.locations.resolve', [
            'workingDirectory' => '/workspace/repo',
            'sessionId' => 'session-1',
        ])
        ->twice()
        ->andReturn(['locationKey' => 'repo:/workspace/repo', 'locationType' => 'repo']);
    $client->shouldReceive('request')
        ->with('session.permissions.locations.apply', [
            'workingDirectory' => '/workspace/repo',
            'sessionId' => 'session-1',
        ])
        ->twice()
        ->andReturn([
            'appliedDirectoryCount' => 2,
            'appliedRuleCount' => 1,
            'appliedRules' => [['tool' => 'bash', 'decision' => 'allow']],
            'changed' => true,
            'locationKey' => 'repo:/workspace/repo',
            'locationType' => 'repo',
        ]);
    $client->shouldReceive('request')
        ->with('session.permissions.locations.addToolApproval', [
            'approval' => ['kind' => 'commands', 'commandIdentifiers' => ['bash']],
            'locationKey' => 'repo:/workspace/repo',
            'sessionId' => 'session-1',
        ])
        ->twice()
        ->andReturn(['success' => true]);
    $client->shouldReceive('request')
        ->with('session.permissions.folderTrust.isTrusted', [
            'path' => '/workspace/repo',
            'sessionId' => 'session-1',
        ])
        ->twice()
        ->andReturn(['trusted' => true]);
    $client->shouldReceive('request')
        ->with('session.permissions.folderTrust.addTrusted', [
            'path' => '/workspace/repo',
            'sessionId' => 'session-1',
        ])
        ->twice()
        ->andReturn(['success' => true]);

    $permissions = new PendingPermissions($client, 'session-1');
    $locations = $permissions->locations();
    $folderTrust = $permissions->folderTrust();

    $resolved = $locations->resolve(new PermissionLocationResolveParams('/workspace/repo'));
    $resolvedFromArray = $locations->resolve(['workingDirectory' => '/workspace/repo']);
    $applied = $locations->apply(new PermissionLocationApplyParams('/workspace/repo'));
    $appliedFromArray = $locations->apply(['workingDirectory' => '/workspace/repo']);
    $approval = $locations->addToolApproval(new PermissionLocationAddToolApprovalParams(
        approval: new PermissionsLocationsAddToolApprovalDetailsCommands(['bash']),
        locationKey: 'repo:/workspace/repo',
    ));
    $approvalFromArray = $locations->addToolApproval([
        'approval' => ['kind' => 'commands', 'commandIdentifiers' => ['bash']],
        'locationKey' => 'repo:/workspace/repo',
    ]);
    $trusted = $folderTrust->isTrusted(new FolderTrustCheckParams('/workspace/repo'));
    $trustedFromArray = $folderTrust->isTrusted(['path' => '/workspace/repo']);
    $added = $folderTrust->addTrusted(new FolderTrustAddParams('/workspace/repo'));
    $addedFromArray = $folderTrust->addTrusted(['path' => '/workspace/repo']);

    expect($resolved)->toBeInstanceOf(PermissionLocationResolveResult::class)
        ->and($resolved->locationType)->toBe(PermissionLocationType::Repo)
        ->and($resolvedFromArray->toArray())->toBe($resolved->toArray())
        ->and($applied)->toBeInstanceOf(PermissionLocationApplyResult::class)
        ->and($applied->appliedDirectoryCount)->toBe(2)
        ->and($applied->appliedRuleCount)->toBe(1)
        ->and($applied->appliedRules)->toBe([['tool' => 'bash', 'decision' => 'allow']])
        ->and($appliedFromArray->toArray())->toBe($applied->toArray())
        ->and($approval)->toBeInstanceOf(PermissionLocationAddToolApprovalResult::class)
        ->and($approval->success)->toBeTrue()
        ->and($approvalFromArray->toArray())->toBe($approval->toArray())
        ->and($trusted)->toBeInstanceOf(FolderTrustCheckResult::class)
        ->and($trusted->trusted)->toBeTrue()
        ->and($trustedFromArray->toArray())->toBe($trusted->toArray())
        ->and($added)->toBeInstanceOf(FolderTrustAddResult::class)
        ->and($added->success)->toBeTrue()
        ->and($addedFromArray->toArray())->toBe($added->toArray());
});
