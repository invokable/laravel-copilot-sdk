<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\CloudTasksCreateParams;
use Revolution\Copilot\Types\Rpc\CloudTasksCreateResult;
use Revolution\Copilot\Types\Rpc\CloudTasksDeleteResult;
use Revolution\Copilot\Types\Rpc\CloudTasksGetResult;
use Revolution\Copilot\Types\Rpc\CloudTasksGetSessionLogsResult;
use Revolution\Copilot\Types\Rpc\CloudTasksGetSessionReplayResult;
use Revolution\Copilot\Types\Rpc\CloudTasksResolveTaskIdResult;
use Revolution\Copilot\Types\Rpc\CloudTasksSetSharingStatusParams;
use Revolution\Copilot\Types\Rpc\CloudTasksSharingStatusResult;

describe('PendingServerCloudTasks', function () {
    it('dispatches all cloudTasks methods with typed parameters and results', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('cloudTasks.create', [
                'baseUrl' => 'https://mission-control.example',
                'authInfo' => ['account' => 'octo'],
                'integrationId' => 'copilot-sdk',
                'repositoryOwner' => 'octo',
                'repositoryName' => 'example',
            ])
            ->andReturn([
                'ok' => false,
                'reason' => 'policy_blocked',
                'message' => 'The task was declined.',
                'statusCode' => 403,
                'errorMessage' => 'Forbidden',
                'errorPayloadJson' => '{"message":"Forbidden"}',
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('cloudTasks.delete', [
                'authInfo' => null,
                'integrationId' => 'copilot-sdk',
                'taskId' => 'task-1',
            ])
            ->andReturn([
                'ok' => false,
                'statusCode' => 404,
                'errorMessage' => 'Task not found.',
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('cloudTasks.get', [
                'authInfo' => ['token' => 'opaque'],
                'integrationId' => 'copilot-sdk',
                'taskId' => 'task-1',
            ])
            ->andReturn([
                'ok' => false,
                'statusCode' => 404,
                'errorKind' => 'not_found',
                'errorPayloadJson' => '{"message":"Not found"}',
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('cloudTasks.getSessionLogs', [
                'authInfo' => ['token' => 'opaque'],
                'sessionId' => 'session-1',
            ])
            ->andReturn([
                'resolved' => false,
                'ok' => false,
                'debugMessages' => [],
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('cloudTasks.getSessionReplay', [
                'authInfo' => ['token' => 'opaque'],
                'integrationId' => 'copilot-sdk',
                'taskId' => 'task-1',
            ])
            ->andReturn([
                'replayEventsJson' => '[]',
                'observedEventsJson' => '[{"type":"session.start"}]',
                'preStart' => true,
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('cloudTasks.getSharingStatus', [
                'authInfo' => ['token' => 'opaque'],
                'integrationId' => 'copilot-sdk',
                'taskId' => 'task-1',
            ])
            ->andReturn([
                'credentialResolved' => false,
                'ok' => false,
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('cloudTasks.setSharingStatus', [
                'authInfo' => ['token' => 'opaque'],
                'integrationId' => 'copilot-sdk',
                'taskId' => 'task-1',
                'sharingStatus' => 'public',
            ])
            ->andReturn([
                'credentialResolved' => true,
                'ok' => true,
                'sharingStatus' => 'public',
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('cloudTasks.resolveTaskId', [
                'taskId' => 'task-1',
                'authInfo' => ['token' => 'opaque'],
                'integrationId' => 'copilot-sdk',
                'allowEmptyCliTask' => false,
                'hasRemoteSessionManager' => true,
                'ignoredLocalSessionIds' => [],
            ])
            ->andReturn([
                'kind' => 'error',
                'errorMessage' => 'Lookup failed.',
                'errorStatusCode' => 502,
                'errorKind' => 'http_error',
                'errorPayloadJson' => '{"message":"Bad gateway"}',
            ]);

        $cloudTasks = (new ServerRpc($client))->cloudTasks();
        $create = $cloudTasks->create(new CloudTasksCreateParams(
            authInfo: ['account' => 'octo'],
            integrationId: 'copilot-sdk',
            baseUrl: 'https://mission-control.example',
            repositoryOwner: 'octo',
            repositoryName: 'example',
        ));
        $delete = $cloudTasks->delete([
            'authInfo' => null,
            'integrationId' => 'copilot-sdk',
            'taskId' => 'task-1',
        ]);
        $get = $cloudTasks->get([
            'authInfo' => ['token' => 'opaque'],
            'integrationId' => 'copilot-sdk',
            'taskId' => 'task-1',
        ]);
        $logs = $cloudTasks->getSessionLogs([
            'authInfo' => ['token' => 'opaque'],
            'sessionId' => 'session-1',
        ]);
        $replay = $cloudTasks->getSessionReplay([
            'authInfo' => ['token' => 'opaque'],
            'integrationId' => 'copilot-sdk',
            'taskId' => 'task-1',
        ]);
        $getSharingStatus = $cloudTasks->getSharingStatus([
            'authInfo' => ['token' => 'opaque'],
            'integrationId' => 'copilot-sdk',
            'taskId' => 'task-1',
        ]);
        $setSharingStatus = $cloudTasks->setSharingStatus(new CloudTasksSetSharingStatusParams(
            authInfo: ['token' => 'opaque'],
            integrationId: 'copilot-sdk',
            taskId: 'task-1',
            sharingStatus: 'public',
        ));
        $resolve = $cloudTasks->resolveTaskId([
            'taskId' => 'task-1',
            'authInfo' => ['token' => 'opaque'],
            'integrationId' => 'copilot-sdk',
            'allowEmptyCliTask' => false,
            'hasRemoteSessionManager' => true,
            'ignoredLocalSessionIds' => [],
        ]);

        expect($create)->toBeInstanceOf(CloudTasksCreateResult::class);
        expect($create->toArray())->toBe([
            'ok' => false,
            'reason' => 'policy_blocked',
            'message' => 'The task was declined.',
            'statusCode' => 403,
            'errorMessage' => 'Forbidden',
            'errorPayloadJson' => '{"message":"Forbidden"}',
        ]);
        expect($delete)->toBeInstanceOf(CloudTasksDeleteResult::class);
        expect($delete->toArray())->toBe([
            'ok' => false,
            'statusCode' => 404,
            'errorMessage' => 'Task not found.',
        ]);
        expect($get)->toBeInstanceOf(CloudTasksGetResult::class);
        expect($get->toArray())->toBe([
            'ok' => false,
            'statusCode' => 404,
            'errorKind' => 'not_found',
            'errorPayloadJson' => '{"message":"Not found"}',
        ]);
        expect($logs)->toBeInstanceOf(CloudTasksGetSessionLogsResult::class);
        expect($logs->toArray())->toBe([
            'resolved' => false,
            'ok' => false,
            'debugMessages' => [],
        ]);
        expect($replay)->toBeInstanceOf(CloudTasksGetSessionReplayResult::class);
        expect($replay->toArray())->toBe([
            'replayEventsJson' => '[]',
            'observedEventsJson' => '[{"type":"session.start"}]',
            'preStart' => true,
        ]);
        expect($getSharingStatus)->toBeInstanceOf(CloudTasksSharingStatusResult::class);
        expect($getSharingStatus->toArray())->toBe([
            'credentialResolved' => false,
            'ok' => false,
        ]);
        expect($setSharingStatus)->toBeInstanceOf(CloudTasksSharingStatusResult::class);
        expect($setSharingStatus->toArray())->toBe([
            'credentialResolved' => true,
            'ok' => true,
            'sharingStatus' => 'public',
        ]);
        expect($resolve)->toBeInstanceOf(CloudTasksResolveTaskIdResult::class);
        expect($resolve->toArray())->toBe([
            'kind' => 'error',
            'errorMessage' => 'Lookup failed.',
            'errorStatusCode' => 502,
            'errorKind' => 'http_error',
            'errorPayloadJson' => '{"message":"Bad gateway"}',
        ]);
    });
});
