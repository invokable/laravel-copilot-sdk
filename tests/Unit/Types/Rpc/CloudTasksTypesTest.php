<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\CloudTasksCreateParams;
use Revolution\Copilot\Types\Rpc\CloudTasksCreateResult;
use Revolution\Copilot\Types\Rpc\CloudTasksDeleteParams;
use Revolution\Copilot\Types\Rpc\CloudTasksDeleteResult;
use Revolution\Copilot\Types\Rpc\CloudTasksGetParams;
use Revolution\Copilot\Types\Rpc\CloudTasksGetResult;
use Revolution\Copilot\Types\Rpc\CloudTasksGetSessionLogsParams;
use Revolution\Copilot\Types\Rpc\CloudTasksGetSessionLogsResult;
use Revolution\Copilot\Types\Rpc\CloudTasksGetSessionReplayParams;
use Revolution\Copilot\Types\Rpc\CloudTasksGetSessionReplayResult;
use Revolution\Copilot\Types\Rpc\CloudTasksGetSharingStatusParams;
use Revolution\Copilot\Types\Rpc\CloudTasksResolveTaskIdParams;
use Revolution\Copilot\Types\Rpc\CloudTasksResolveTaskIdResult;
use Revolution\Copilot\Types\Rpc\CloudTasksSetSharingStatusParams;
use Revolution\Copilot\Types\Rpc\CloudTasksSharingStatusResult;

describe('cloudTasks RPC types', function () {
    it('round-trips parameter and result payloads', function (array $payload, string $type) {
        $dto = $type::fromArray($payload);

        expect($dto)->toBeInstanceOf($type)
            ->and($dto->toArray())->toBe($payload);
    })->with([
        'create parameters' => [[
            'baseUrl' => 'https://mission-control.example',
            'authInfo' => ['account' => ['id' => 42]],
            'authContextId' => 'account-1',
            'integrationId' => 'copilot-sdk',
            'repositoryOwner' => 'octo',
            'repositoryName' => 'example',
            'owner' => 'octo',
        ], CloudTasksCreateParams::class],
        'create result' => [[
            'ok' => false,
            'responseJson' => '{"id":"task-1"}',
            'reason' => 'policy_blocked',
            'message' => 'The task was declined.',
            'statusCode' => 403,
            'errorMessage' => 'Forbidden',
            'errorPayloadJson' => '{"message":"Forbidden"}',
        ], CloudTasksCreateResult::class],
        'delete parameters' => [[
            'baseUrl' => 'https://mission-control.example',
            'authInfo' => null,
            'authContextId' => 'account-1',
            'integrationId' => 'copilot-sdk',
            'taskId' => 'task-1',
        ], CloudTasksDeleteParams::class],
        'delete result' => [[
            'ok' => false,
            'statusCode' => 404,
            'errorMessage' => 'Task not found.',
            'errorPayloadJson' => '{"message":"Not found"}',
        ], CloudTasksDeleteResult::class],
        'get parameters' => [[
            'authInfo' => ['token' => 'opaque'],
            'authContextId' => 'account-1',
            'integrationId' => 'copilot-sdk',
            'taskId' => 'task-1',
        ], CloudTasksGetParams::class],
        'get result' => [[
            'ok' => false,
            'payloadJson' => '{"id":"task-1"}',
            'statusCode' => 404,
            'errorMessage' => 'Task not found.',
            'errorKind' => 'not_found',
            'errorPayloadJson' => '{"message":"Not found"}',
        ], CloudTasksGetResult::class],
        'session logs parameters' => [[
            'authInfo' => ['token' => 'opaque'],
            'authContextId' => 'account-1',
            'sessionId' => 'session-1',
        ], CloudTasksGetSessionLogsParams::class],
        'session logs result' => [[
            'resolved' => true,
            'ok' => false,
            'logsJson' => '[]',
            'debugMessages' => ['Retrying request'],
            'errorString' => 'Response could not be decoded.',
            'statusCode' => 502,
            'errorMessage' => 'Bad gateway',
            'errorKind' => 'http_error',
            'errorPayloadJson' => '{"message":"Bad gateway"}',
        ], CloudTasksGetSessionLogsResult::class],
        'session replay parameters' => [[
            'baseUrl' => 'https://mission-control.example',
            'authInfo' => ['token' => 'opaque'],
            'authContextId' => 'account-1',
            'integrationId' => 'copilot-sdk',
            'taskId' => 'task-1',
        ], CloudTasksGetSessionReplayParams::class],
        'session replay result' => [[
            'replayEventsJson' => '[]',
            'observedEventsJson' => '[{"type":"session.start"}]',
            'preStart' => true,
        ], CloudTasksGetSessionReplayResult::class],
        'get sharing status parameters' => [[
            'baseUrl' => 'https://mission-control.example',
            'authInfo' => ['token' => 'opaque'],
            'authContextId' => 'account-1',
            'integrationId' => 'copilot-sdk',
            'taskId' => 'task-1',
        ], CloudTasksGetSharingStatusParams::class],
        'sharing status result' => [[
            'credentialResolved' => false,
            'ok' => false,
            'sharingStatus' => 'private',
        ], CloudTasksSharingStatusResult::class],
        'set sharing status parameters' => [[
            'baseUrl' => 'https://mission-control.example',
            'authInfo' => ['token' => 'opaque'],
            'authContextId' => 'account-1',
            'integrationId' => 'copilot-sdk',
            'taskId' => 'task-1',
            'sharingStatus' => 'private',
        ], CloudTasksSetSharingStatusParams::class],
        'resolve task parameters' => [[
            'taskId' => 'task-1',
            'authInfo' => ['token' => 'opaque'],
            'authContextId' => 'account-1',
            'integrationId' => 'copilot-sdk',
            'debugGithubApiUrl' => 'https://api.example',
            'sessionsHome' => '/tmp/sessions',
            'allowEmptyCliTask' => false,
            'hasRemoteSessionManager' => true,
            'ignoredLocalSessionIds' => [],
        ], CloudTasksResolveTaskIdParams::class],
        'resolve task result' => [[
            'kind' => 'error',
            'sessionId' => 'session-1',
            'metadataJson' => '{"id":"remote-session-1"}',
            'diagnostic' => 'Local session could not be read.',
            'errorMessage' => 'Lookup failed.',
            'errorStatusCode' => 502,
            'errorKind' => 'http_error',
            'errorPayloadJson' => '{"message":"Bad gateway"}',
        ], CloudTasksResolveTaskIdResult::class],
    ]);

    it('omits absent optional fields while preserving nullable authInfo and false values', function () {
        $params = CloudTasksCreateParams::fromArray([
            'authInfo' => null,
            'integrationId' => 'copilot-sdk',
        ]);
        $resolveParams = CloudTasksResolveTaskIdParams::fromArray([
            'taskId' => 'task-1',
            'authInfo' => false,
            'integrationId' => 'copilot-sdk',
            'allowEmptyCliTask' => false,
            'hasRemoteSessionManager' => false,
            'ignoredLocalSessionIds' => [],
        ]);

        expect($params->toArray())->toBe([
            'authInfo' => null,
            'integrationId' => 'copilot-sdk',
        ]);
        expect($resolveParams->toArray())->toBe([
            'taskId' => 'task-1',
            'authInfo' => false,
            'integrationId' => 'copilot-sdk',
            'allowEmptyCliTask' => false,
            'hasRemoteSessionManager' => false,
            'ignoredLocalSessionIds' => [],
        ]);
    });
});
