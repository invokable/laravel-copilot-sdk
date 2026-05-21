<?php

declare(strict_types=1);

use Revolt\EventLoop;
use Revolution\Copilot\Contracts\Transport;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\JsonRpc\JsonRpcMessage;
use Revolution\Copilot\Rpc\PendingPermissions;
use Revolution\Copilot\Rpc\PendingPermissionsPaths;
use Revolution\Copilot\Rpc\PendingPermissionsUrls;
use Revolution\Copilot\Types\Rpc\PendingPermissionRequestList;
use Revolution\Copilot\Types\Rpc\PermissionDecisionRequest;
use Revolution\Copilot\Types\Rpc\PermissionPathsAddParams;
use Revolution\Copilot\Types\Rpc\PermissionPathsAllowedCheckResult;
use Revolution\Copilot\Types\Rpc\PermissionPathsList;
use Revolution\Copilot\Types\Rpc\PermissionPathsUpdatePrimaryParams;
use Revolution\Copilot\Types\Rpc\PermissionPathsWorkspaceCheckResult;
use Revolution\Copilot\Types\Rpc\PermissionPromptShownNotification;
use Revolution\Copilot\Types\Rpc\PermissionRequestResult;
use Revolution\Copilot\Types\Rpc\PermissionsConfigureParams;
use Revolution\Copilot\Types\Rpc\PermissionsConfigureResult;
use Revolution\Copilot\Types\Rpc\PermissionsModifyRulesParams;
use Revolution\Copilot\Types\Rpc\PermissionsModifyRulesResult;
use Revolution\Copilot\Types\Rpc\PermissionsNotifyPromptShownResult;
use Revolution\Copilot\Types\Rpc\PermissionsPathsAddResult;
use Revolution\Copilot\Types\Rpc\PermissionsPathsUpdatePrimaryResult;
use Revolution\Copilot\Types\Rpc\PermissionsResetSessionApprovalsResult;
use Revolution\Copilot\Types\Rpc\PermissionsSetApproveAllRequest;
use Revolution\Copilot\Types\Rpc\PermissionsSetApproveAllResult;
use Revolution\Copilot\Types\Rpc\PermissionsSetRequiredRequest;
use Revolution\Copilot\Types\Rpc\PermissionsSetRequiredResult;
use Revolution\Copilot\Types\Rpc\PermissionsUrlsSetUnrestrictedModeResult;
use Revolution\Copilot\Types\Rpc\PermissionUrlsSetUnrestrictedModeParams;

/**
 * Build a started JsonRpcClient backed by a mock transport.
 * Captured outgoing messages are appended to $sentMessages.
 * Each request automatically receives a successful {"success":true} response.
 */
function makePermissionsClient(array &$sentMessages): JsonRpcClient
{
    $handler = null;

    $transport = Mockery::mock(Transport::class);
    $transport->shouldReceive('onReceive')->andReturnUsing(function ($callback) use (&$handler) {
        $handler = $callback;
    });
    $transport->shouldReceive('start')->once();
    $transport->shouldReceive('send')->andReturnUsing(function ($message) use (&$sentMessages, &$handler) {
        $sentMessages[] = $message;
        if (preg_match('/\{.*\}/s', $message, $matches)) {
            $request = json_decode($matches[0], true);
            if (isset($request['id'])) {
                $result = match ($request['method']) {
                    'session.permissions.pendingRequests' => ['items' => []],
                    'session.permissions.paths.list' => ['directories' => ['/workspace'], 'primary' => '/workspace'],
                    'session.permissions.paths.isPathWithinAllowedDirectories', 'session.permissions.paths.isPathWithinWorkspace' => ['allowed' => true],
                    default => ['success' => true],
                };
                $response = JsonRpcMessage::response($request['id'], $result);
                EventLoop::queue(function () use (&$handler, $response) {
                    if ($handler !== null) {
                        ($handler)($response->toJson());
                    }
                });
            }
        }
    });

    $client = new JsonRpcClient($transport);
    $client->start();

    return $client;
}

/**
 * Extract and decode the JSON payload from a Content-Length framed message.
 */
function decodePermissionsJsonMessage(string $message): array
{
    preg_match('/\{.*\}/s', $message, $matches);

    return json_decode($matches[0], true);
}

describe('PendingSessionPermissions', function () {
    it('sends correct method name for handlePendingPermissionRequest', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);

        $pending = new PendingPermissions($client, 'session-abc');
        $pending->handlePendingPermissionRequest(new PermissionDecisionRequest(
            requestId: 'req-1',
            result: ['kind' => 'approve-once'],
        ));

        expect($sentMessages)->toHaveCount(1)
            ->and($sentMessages[0])->toContain('"method":"session.permissions.handlePendingPermissionRequest"');
    });

    it('injects sessionId into request params', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);

        $pending = new PendingPermissions($client, 'session-abc');
        $pending->handlePendingPermissionRequest(new PermissionDecisionRequest(
            requestId: 'req-1',
            result: ['kind' => 'approve-once'],
        ));

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($decoded['params']['sessionId'])->toBe('session-abc');
    });

    it('includes requestId and result in request params', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);

        $pending = new PendingPermissions($client, 'session-abc');
        $pending->handlePendingPermissionRequest(new PermissionDecisionRequest(
            requestId: 'perm-req-42',
            result: ['kind' => 'reject'],
        ));

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($decoded['params']['requestId'])->toBe('perm-req-42')
            ->and($decoded['params']['result'])->toBe(['kind' => 'reject']);
    });

    it('maps the response to PermissionRequestResult', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);

        $pending = new PendingPermissions($client, 'session-abc');
        $result = $pending->handlePendingPermissionRequest(new PermissionDecisionRequest(
            requestId: 'req-1',
            result: ['kind' => 'approve-once'],
        ));

        expect($result)->toBeInstanceOf(PermissionRequestResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('accepts array params instead of class instance', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);

        $pending = new PendingPermissions($client, 'session-xyz');
        $result = $pending->handlePendingPermissionRequest([
            'requestId' => 'req-array',
            'result' => ['kind' => 'approve-once'],
        ]);

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($result)->toBeInstanceOf(PermissionRequestResult::class)
            ->and($decoded['params']['sessionId'])->toBe('session-xyz')
            ->and($decoded['params']['requestId'])->toBe('req-array');
    });

    it('setApproveAll sends correct method and returns result', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);

        $pending = new PendingPermissions($client, 'session-abc');
        $result = $pending->setApproveAll(new PermissionsSetApproveAllRequest(enabled: true));

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($result)->toBeInstanceOf(PermissionsSetApproveAllResult::class)
            ->and($decoded['method'])->toBe('session.permissions.setApproveAll')
            ->and($decoded['params']['sessionId'])->toBe('session-abc')
            ->and($decoded['params']['enabled'])->toBeTrue();
    });

    it('setApproveAll accepts array params', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);

        $pending = new PendingPermissions($client, 'session-abc');
        $result = $pending->setApproveAll(['enabled' => false]);

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($result)->toBeInstanceOf(PermissionsSetApproveAllResult::class)
            ->and($decoded['params']['enabled'])->toBeFalse();
    });

    it('resetSessionApprovals sends correct method and returns result', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);

        $pending = new PendingPermissions($client, 'session-abc');
        $result = $pending->resetSessionApprovals();

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($result)->toBeInstanceOf(PermissionsResetSessionApprovalsResult::class)
            ->and($decoded['method'])->toBe('session.permissions.resetSessionApprovals')
            ->and($decoded['params']['sessionId'])->toBe('session-abc');
    });

    it('returns nested permissions paths and urls RPC handlers', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);
        $pending = new PendingPermissions($client, 'session-abc');

        expect($pending->paths())->toBeInstanceOf(PendingPermissionsPaths::class)
            ->and($pending->urls())->toBeInstanceOf(PendingPermissionsUrls::class);
    });

    it('configure sends method and returns result', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);
        $pending = new PendingPermissions($client, 'session-abc');

        $result = $pending->configure(new PermissionsConfigureParams(
            approveAllToolPermissionRequests: true,
        ));

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($result)->toBeInstanceOf(PermissionsConfigureResult::class)
            ->and($decoded['method'])->toBe('session.permissions.configure')
            ->and($decoded['params']['sessionId'])->toBe('session-abc')
            ->and($decoded['params']['approveAllToolPermissionRequests'])->toBeTrue();
    });

    it('pendingRequests sends method and maps list result', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);
        $pending = new PendingPermissions($client, 'session-abc');

        $result = $pending->pendingRequests();

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($result)->toBeInstanceOf(PendingPermissionRequestList::class)
            ->and($decoded['method'])->toBe('session.permissions.pendingRequests')
            ->and($decoded['params']['sessionId'])->toBe('session-abc')
            ->and($result->items)->toBe([]);
    });

    it('modifyRules sends method and maps result', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);
        $pending = new PendingPermissions($client, 'session-abc');

        $result = $pending->modifyRules(new PermissionsModifyRulesParams(scope: 'session', removeAll: true));

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($result)->toBeInstanceOf(PermissionsModifyRulesResult::class)
            ->and($decoded['method'])->toBe('session.permissions.modifyRules')
            ->and($decoded['params']['scope'])->toBe('session')
            ->and($decoded['params']['removeAll'])->toBeTrue()
            ->and($decoded['params']['sessionId'])->toBe('session-abc');
    });

    it('setRequired sends method and maps result', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);
        $pending = new PendingPermissions($client, 'session-abc');

        $result = $pending->setRequired(new PermissionsSetRequiredRequest(required: true));

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($result)->toBeInstanceOf(PermissionsSetRequiredResult::class)
            ->and($decoded['method'])->toBe('session.permissions.setRequired')
            ->and($decoded['params']['required'])->toBeTrue()
            ->and($decoded['params']['sessionId'])->toBe('session-abc');
    });

    it('notifyPromptShown sends method and maps result', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);
        $pending = new PendingPermissions($client, 'session-abc');

        $result = $pending->notifyPromptShown(new PermissionPromptShownNotification(message: 'Prompt was shown'));

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($result)->toBeInstanceOf(PermissionsNotifyPromptShownResult::class)
            ->and($decoded['method'])->toBe('session.permissions.notifyPromptShown')
            ->and($decoded['params']['message'])->toBe('Prompt was shown')
            ->and($decoded['params']['sessionId'])->toBe('session-abc');
    });

    it('paths list/add/update/check methods send correct RPC methods', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);
        $paths = (new PendingPermissions($client, 'session-abc'))->paths();

        $list = $paths->list();
        $add = $paths->add(new PermissionPathsAddParams(path: '/tmp/workspace'));
        $update = $paths->updatePrimary(new PermissionPathsUpdatePrimaryParams(path: '/tmp/workspace'));
        $allowed = $paths->isPathWithinAllowedDirectories(['path' => '/tmp/workspace/file.txt']);
        $workspace = $paths->isPathWithinWorkspace(['path' => '/tmp/workspace/file.txt']);

        expect($list)->toBeInstanceOf(PermissionPathsList::class)
            ->and($add)->toBeInstanceOf(PermissionsPathsAddResult::class)
            ->and($update)->toBeInstanceOf(PermissionsPathsUpdatePrimaryResult::class)
            ->and($allowed)->toBeInstanceOf(PermissionPathsAllowedCheckResult::class)
            ->and($workspace)->toBeInstanceOf(PermissionPathsWorkspaceCheckResult::class)
            ->and(array_map(
                fn (string $message): string => decodePermissionsJsonMessage($message)['method'],
                $sentMessages,
            ))->toBe([
                'session.permissions.paths.list',
                'session.permissions.paths.add',
                'session.permissions.paths.updatePrimary',
                'session.permissions.paths.isPathWithinAllowedDirectories',
                'session.permissions.paths.isPathWithinWorkspace',
            ]);
    });

    it('urls setUnrestrictedMode sends correct method and maps result', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);
        $urls = (new PendingPermissions($client, 'session-abc'))->urls();

        $result = $urls->setUnrestrictedMode(new PermissionUrlsSetUnrestrictedModeParams(enabled: true));

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($result)->toBeInstanceOf(PermissionsUrlsSetUnrestrictedModeResult::class)
            ->and($decoded['method'])->toBe('session.permissions.urls.setUnrestrictedMode')
            ->and($decoded['params']['enabled'])->toBeTrue()
            ->and($decoded['params']['sessionId'])->toBe('session-abc');
    });
});
