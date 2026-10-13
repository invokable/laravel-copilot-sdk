<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\RemoteControlConfig;
use Revolution\Copilot\Types\Rpc\RemoteControlConfigExistingMcSession;
use Revolution\Copilot\Types\Rpc\RemoteControlStatus;
use Revolution\Copilot\Types\Rpc\RemoteControlStatusResult;
use Revolution\Copilot\Types\Rpc\RemoteControlStopResult;
use Revolution\Copilot\Types\Rpc\RemoteControlTransferResult;
use Revolution\Copilot\Types\Rpc\RemoteNotifySteerableChangedRequest;
use Revolution\Copilot\Types\Rpc\SessionsSetRemoteControlSteeringRequest;
use Revolution\Copilot\Types\Rpc\SessionsStartRemoteControlRequest;
use Revolution\Copilot\Types\Rpc\SessionsStopRemoteControlRequest;
use Revolution\Copilot\Types\Rpc\SessionsTransferRemoteControlRequest;

describe('Remote Control RPC types', function () {
    it('round trips remote-control configuration and request DTOs', function () {
        $configData = [
            'remote' => false,
            'steerable' => true,
            'explicit' => false,
            'silent' => false,
            'taskId' => 'task-1',
            'existingMcSession' => [
                'mcSessionId' => 'mc-session-1',
                'mcTaskId' => 'mc-task-1',
            ],
        ];
        $config = RemoteControlConfig::fromArray($configData);
        $startRequest = SessionsStartRemoteControlRequest::fromArray([
            'sessionId' => 'session-1',
            'config' => $configData,
        ]);
        $stopRequest = SessionsStopRemoteControlRequest::fromArray([
            'expectedSessionId' => 'session-1',
            'force' => false,
        ]);
        $transferRequest = SessionsTransferRemoteControlRequest::fromArray([
            'toSessionId' => 'session-2',
            'expectedFromSessionId' => 'session-1',
        ]);
        $steeringRequest = SessionsSetRemoteControlSteeringRequest::fromArray(['enabled' => false]);
        $notifyRequest = RemoteNotifySteerableChangedRequest::fromArray(['remoteSteerable' => true]);

        expect($config->existingMcSession)->toBeInstanceOf(RemoteControlConfigExistingMcSession::class)
            ->and($config->toArray())->toBe($configData)
            ->and($startRequest->config)->toBeInstanceOf(RemoteControlConfig::class)
            ->and($startRequest->toArray())->toBe([
                'sessionId' => 'session-1',
                'config' => $configData,
            ])
            ->and($stopRequest->toArray())->toBe([
                'expectedSessionId' => 'session-1',
                'force' => false,
            ])
            ->and($transferRequest->toArray())->toBe([
                'toSessionId' => 'session-2',
                'expectedFromSessionId' => 'session-1',
            ])
            ->and($steeringRequest->toArray())->toBe(['enabled' => false])
            ->and($notifyRequest->toArray())->toBe(['remoteSteerable' => true]);
    });

    it('omits absent remote-control configuration and request options', function () {
        expect((new RemoteControlConfig)->toArray())->toBe([])
            ->and((new SessionsStopRemoteControlRequest)->toArray())->toBe([])
            ->and((new SessionsTransferRemoteControlRequest(toSessionId: 'session-2'))->toArray())
            ->toBe(['toSessionId' => 'session-2']);
    });

    it('maps every remote-control state and ignores opaque fields', function () {
        $statuses = [
            [
                ['state' => 'off'],
                ['state' => 'off'],
            ],
            [
                ['state' => 'connecting', 'attachedSessionId' => 'session-1'],
                ['state' => 'connecting', 'attachedSessionId' => 'session-1'],
            ],
            [
                [
                    'state' => 'active',
                    'attachedSessionId' => 'session-1',
                    'frontendUrl' => 'https://github.com/mission-control/session/1',
                    'isSteerable' => true,
                    'awaitingFirstMessage' => false,
                    'promptManager' => ['opaque' => true],
                ],
                [
                    'state' => 'active',
                    'attachedSessionId' => 'session-1',
                    'frontendUrl' => 'https://github.com/mission-control/session/1',
                    'isSteerable' => true,
                    'awaitingFirstMessage' => false,
                ],
            ],
            [
                [
                    'state' => 'error',
                    'error' => 'Remote export failed',
                    'attachedSessionId' => 'session-1',
                ],
                [
                    'state' => 'error',
                    'attachedSessionId' => 'session-1',
                    'error' => 'Remote export failed',
                ],
            ],
        ];

        foreach ($statuses as [$data, $expected]) {
            expect(RemoteControlStatus::fromArray($data)->toArray())->toBe($expected);
        }
    });

    it('round trips remote-control status result wrappers', function () {
        $status = [
            'state' => 'active',
            'attachedSessionId' => 'session-1',
            'frontendUrl' => 'https://github.com/mission-control/session/1',
            'isSteerable' => true,
        ];
        $statusResult = RemoteControlStatusResult::fromArray(['status' => $status]);
        $stopResult = RemoteControlStopResult::fromArray([
            'status' => ['state' => 'off'],
            'stopped' => false,
        ]);
        $transferResult = RemoteControlTransferResult::fromArray([
            'status' => $status,
            'transferred' => true,
        ]);

        expect($statusResult->status)->toBeInstanceOf(RemoteControlStatus::class)
            ->and($statusResult->toArray())->toBe(['status' => $status])
            ->and($stopResult->toArray())->toBe([
                'status' => ['state' => 'off'],
                'stopped' => false,
            ])
            ->and($transferResult->toArray())->toBe([
                'status' => $status,
                'transferred' => true,
            ]);
    });
});
