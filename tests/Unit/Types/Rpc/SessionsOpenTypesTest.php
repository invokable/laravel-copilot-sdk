<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionsOpenHandoffTaskType;
use Revolution\Copilot\Enums\SessionsOpenStatus;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\Rpc\SessionOpenOptions;
use Revolution\Copilot\Types\Rpc\SessionsOpenAttachRequest;
use Revolution\Copilot\Types\Rpc\SessionsOpenCloudRequest;
use Revolution\Copilot\Types\Rpc\SessionsOpenCreateRequest;
use Revolution\Copilot\Types\Rpc\SessionsOpenHandoffRequest;
use Revolution\Copilot\Types\Rpc\SessionsOpenProgress;
use Revolution\Copilot\Types\Rpc\SessionsOpenRemoteRequest;
use Revolution\Copilot\Types\Rpc\SessionsOpenRequest;
use Revolution\Copilot\Types\Rpc\SessionsOpenResult;
use Revolution\Copilot\Types\Rpc\SessionsOpenResumeLastRequest;
use Revolution\Copilot\Types\Rpc\SessionsOpenResumeRequest;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionContext;

describe('sessions.open request types', function () {
    it('round trips all request variants and keeps their wire discriminators', function () {
        $requests = [
            ['kind' => 'create', 'options' => ['model' => 'gpt-5'], 'emitStart' => false],
            [
                'kind' => 'resume',
                'sessionId' => 'session-1',
                'resume' => false,
                'suppressResumeWorkspaceMetadataWriteback' => true,
            ],
            [
                'kind' => 'resumeLast',
                'context' => ['cwd' => '/workspace', 'branch' => 'main', 'hostType' => 'github'],
            ],
            ['kind' => 'attach', 'sessionId' => 'session-2'],
            [
                'kind' => 'remote',
                'remoteSessionId' => 'remote-1',
                'repository' => ['owner' => 'org', 'name' => 'repo'],
            ],
            ['kind' => 'cloud', 'owner' => 'org'],
            [
                'kind' => 'handoff',
                'metadata' => ['sessionId' => 'remote-2'],
                'taskType' => 'cca',
            ],
        ];

        foreach ($requests as $request) {
            expect(SessionsOpenRequest::fromArray($request)->toArray())->toBe($request);
        }
    });

    it('serializes existing configs to supported options without callbacks', function () {
        $config = new SessionConfig(
            sessionId: 'custom-session',
            model: 'gpt-5',
            modelCapabilities: ['supports' => ['vision' => true]],
            enableExperimentalMode: true,
            streaming: false,
            onPermissionRequest: fn () => true,
            onEvent: fn () => null,
        );

        expect((new SessionOpenOptions($config))->toArray())->toBe([
            'sessionId' => 'custom-session',
            'model' => 'gpt-5',
            'isExperimentalMode' => true,
            'modelCapabilitiesOverrides' => ['supports' => ['vision' => true]],
            'enableStreaming' => false,
        ]);
    });

    it('accepts wire options while excluding out-of-process callback fields', function () {
        $request = SessionsOpenRequest::fromArray([
            'kind' => 'create',
            'options' => [
                'lspClientName' => 'laravel-host',
                'sessionApi' => new stdClass,
                'onTaskCreated' => fn () => null,
            ],
        ]);
        $cloud = SessionsOpenRequest::fromArray([
            'kind' => 'cloud',
            'onTaskCreated' => fn () => null,
        ]);
        $handoff = SessionsOpenRequest::fromArray([
            'kind' => 'handoff',
            'metadata' => ['sessionId' => 'remote-session'],
            'onProgress' => fn () => null,
            'onConfirm' => fn () => true,
        ]);

        expect($request->toArray())->toBe([
            'kind' => 'create',
            'options' => ['lspClientName' => 'laravel-host'],
        ])->and($cloud->toArray())->toBe([
            'kind' => 'cloud',
        ])->and($handoff->toArray())->toBe([
            'kind' => 'handoff',
            'metadata' => ['sessionId' => 'remote-session'],
        ]);
    });

    it('converts typed config and context values in variants', function () {
        $create = new SessionsOpenCreateRequest(
            options: new SessionConfig(model: 'gpt-5', onPermissionRequest: fn () => true),
        );
        $resume = new SessionsOpenResumeRequest(
            sessionId: 'session-3',
            options: new ResumeSessionConfig(
                reasoningEffort: 'high',
                suppressResumeEvent: true,
            ),
        );
        $resumeLast = new SessionsOpenResumeLastRequest(
            context: new SessionContext(cwd: '/workspace'),
        );
        $attach = new SessionsOpenAttachRequest(sessionId: 'session-3');
        $remote = new SessionsOpenRemoteRequest(remoteSessionId: 'remote-3');
        $cloud = new SessionsOpenCloudRequest(owner: 'org');
        $handoff = new SessionsOpenHandoffRequest(
            metadata: ['sessionId' => 'remote-3'],
            taskType: SessionsOpenHandoffTaskType::Cca,
        );

        expect($create->toArray())->toBe([
            'kind' => 'create',
            'options' => ['model' => 'gpt-5'],
        ])->and($resume->toArray())->toBe([
            'kind' => 'resume',
            'sessionId' => 'session-3',
            'options' => ['reasoningEffort' => 'high'],
            'resume' => false,
        ])->and($resumeLast->toArray())->toBe([
            'kind' => 'resumeLast',
            'context' => ['cwd' => '/workspace'],
        ])->and($attach->toArray())->toBe([
            'kind' => 'attach',
            'sessionId' => 'session-3',
        ])->and($remote->toArray())->toBe([
            'kind' => 'remote',
            'remoteSessionId' => 'remote-3',
        ])->and($cloud->toArray())->toBe([
            'kind' => 'cloud',
            'owner' => 'org',
        ])->and($handoff->toArray())->toBe([
            'kind' => 'handoff',
            'metadata' => ['sessionId' => 'remote-3'],
            'taskType' => 'cca',
        ]);
    });
});

describe('SessionsOpenResult', function () {
    it('round trips result fields and omits the in-process session handle', function () {
        $result = SessionsOpenResult::fromArray([
            'status' => 'handed_off',
            'sessionId' => 'local-session',
            'startupPrompts' => ['Review the repository'],
            'remoteSessionId' => 'remote-session',
            'metadata' => ['repository' => ['owner' => 'org', 'name' => 'repo']],
            'progress' => [[
                'step' => 'save-session',
                'status' => 'complete',
                'message' => 'Saved',
            ]],
            'sessionApi' => new stdClass,
        ]);

        expect($result->status)->toBe(SessionsOpenStatus::HandedOff)
            ->and($result->sessionId)->toBe('local-session')
            ->and($result->progress[0])->toBeInstanceOf(SessionsOpenProgress::class)
            ->and($result->toArray())->toBe([
                'status' => 'handed_off',
                'sessionId' => 'local-session',
                'startupPrompts' => ['Review the repository'],
                'remoteSessionId' => 'remote-session',
                'metadata' => ['repository' => ['owner' => 'org', 'name' => 'repo']],
                'progress' => [[
                    'step' => 'save-session',
                    'status' => 'complete',
                    'message' => 'Saved',
                ]],
            ])
            ->and(json_encode($result->toArray()))->not->toContain('sessionApi');
    });

    it('preserves unknown statuses and omits the session ID for not found', function () {
        $notFound = SessionsOpenResult::fromArray(['status' => 'not_found', 'sessionApi' => new stdClass]);
        $future = SessionsOpenResult::fromArray(['status' => 'future-status']);

        expect($notFound->status)->toBe(SessionsOpenStatus::NotFound)
            ->and($notFound->toArray())->toBe(['status' => 'not_found'])
            ->and($future->status)->toBe('future-status');
    });
});
