<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ContextTier;
use Revolution\Copilot\Enums\PermissionMode;
use Revolution\Copilot\Enums\SessionManagementScope;
use Revolution\Copilot\Types\Rpc\ManagedSessionInfo;
use Revolution\Copilot\Types\Rpc\SessionManagementCloseRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementCloseResult;
use Revolution\Copilot\Types\Rpc\SessionManagementConfigureRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementCreateHostRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementCreateRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementCreateResult;
use Revolution\Copilot\Types\Rpc\SessionManagementListRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementListResult;
use Revolution\Copilot\Types\Rpc\SessionManagementMessageRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementMessageResult;
use Revolution\Copilot\Types\Rpc\SessionManagementPolicy;
use Revolution\Copilot\Types\Rpc\SessionManagementToolOverrides;

describe('session management RPC types', function () {
    it('round trips configure requests and nested tool overrides', function () {
        $data = [
            'cwd' => '/workspace',
            'enabled' => true,
            'scope' => 'in-process',
            'tools' => [
                'close' => false,
                'list' => true,
                'message' => true,
            ],
            'hasCreateHandler' => false,
        ];

        $request = SessionManagementConfigureRequest::fromArray($data);

        expect($request->scope)->toBe(SessionManagementScope::InProcess)
            ->and($request->tools)->toBeInstanceOf(SessionManagementToolOverrides::class)
            ->and($request->toArray())->toBe($data)
            ->and(SessionManagementConfigureRequest::fromArray($request->toArray())->toArray())->toBe($data);
    });

    it('preserves unknown scope values in requests and policy results', function () {
        $request = SessionManagementConfigureRequest::fromArray(['scope' => 'future-scope']);
        $policyData = [
            'enabled' => true,
            'scope' => 'future-scope',
            'list' => true,
            'create' => false,
            'message' => true,
            'close' => false,
        ];
        $policy = SessionManagementPolicy::fromArray($policyData);

        expect($request->scope)->toBe('future-scope')
            ->and($request->toArray())->toBe(['scope' => 'future-scope'])
            ->and($policy->scope)->toBe('future-scope')
            ->and($policy->toArray())->toBe($policyData);
    });

    it('omits absent optional fields while retaining false values', function () {
        $sessionData = [
            'sessionId' => 'session-42',
            'name' => 'worker',
            'cwd' => '/workspace',
            'state' => 'idle',
        ];

        expect(SessionManagementConfigureRequest::fromArray([])->toArray())->toBe([])
            ->and(SessionManagementListRequest::fromArray([])->toArray())->toBe([])
            ->and(SessionManagementToolOverrides::fromArray(['create' => false])->toArray())->toBe(['create' => false])
            ->and(ManagedSessionInfo::fromArray($sessionData)->toArray())->toBe($sessionData)
            ->and(SessionManagementListResult::fromArray([])->toArray())->toBe(['sessions' => []])
            ->and(SessionManagementMessageResult::fromArray(['accepted' => false])->toArray())->toBe(['accepted' => false]);
    });

    it('round trips a filtered peer list and nested session metadata', function () {
        $requestData = ['cwd' => '/workspace'];
        $sessionData = [
            'sessionId' => 'session-42',
            'name' => 'worker',
            'cwd' => '/workspace',
            'sessionStatePath' => '/state/session-42',
            'state' => 'idle',
            'model' => 'gpt-5',
            'parentSessionId' => 'session-parent',
        ];
        $resultData = ['sessions' => [$sessionData]];
        $request = SessionManagementListRequest::fromArray($requestData);
        $result = SessionManagementListResult::fromArray($resultData);

        expect($request->toArray())->toBe($requestData)
            ->and($result->sessions[0])->toBeInstanceOf(ManagedSessionInfo::class)
            ->and($result->toArray())->toBe($resultData)
            ->and(SessionManagementListResult::fromArray($result->toArray())->toArray())->toBe($resultData);
    });

    it('round trips message and close requests and results', function () {
        $messageRequestData = [
            'targetSessionId' => 'session-42',
            'message' => 'Please report progress.',
        ];
        $messageResultData = [
            'accepted' => true,
            'targetSessionName' => 'worker',
        ];
        $closeRequestData = ['targetSessionId' => 'session-42'];
        $closeResultData = ['closed' => true];

        expect(SessionManagementMessageRequest::fromArray($messageRequestData)->toArray())->toBe($messageRequestData)
            ->and(SessionManagementMessageResult::fromArray($messageResultData)->toArray())->toBe($messageResultData)
            ->and(SessionManagementCloseRequest::fromArray($closeRequestData)->toArray())->toBe($closeRequestData)
            ->and(SessionManagementCloseResult::fromArray($closeResultData)->toArray())->toBe($closeResultData);
    });

    it('round trips create requests, host callback requests, and results', function () {
        $createRequestData = [
            'prompt' => 'Review the implementation.',
            'name' => 'reviewer',
            'cwd' => '/workspace',
            'model' => 'gpt-5',
            'reasoningEffort' => 'high',
            'contextTier' => 'long_context',
        ];
        $hostRequestData = [
            'sessionId' => 'session-parent',
            'parentSessionId' => 'session-parent',
            'newSessionId' => 'session-child',
            'cwd' => '/workspace',
            'configDir' => '/config',
            'permissionMode' => 'manual',
            'model' => 'gpt-5',
            'providerId' => 'provider-1',
            'reasoningEffort' => 'high',
            'contextTier' => 'long_context',
            'allowedModels' => ['gpt-5'],
            'name' => 'reviewer',
        ];
        $resultData = ['sessionId' => 'session-child'];
        $createRequest = SessionManagementCreateRequest::fromArray($createRequestData);
        $hostRequest = SessionManagementCreateHostRequest::fromArray($hostRequestData);

        expect($createRequest->contextTier)->toBe(ContextTier::LONG_CONTEXT)
            ->and($createRequest->toArray())->toBe($createRequestData)
            ->and($hostRequest->permissionMode)->toBe(PermissionMode::Manual)
            ->and($hostRequest->contextTier)->toBe(ContextTier::LONG_CONTEXT)
            ->and($hostRequest->toArray())->toBe($hostRequestData)
            ->and(SessionManagementCreateResult::fromArray($resultData)->toArray())->toBe($resultData);
    });
});
