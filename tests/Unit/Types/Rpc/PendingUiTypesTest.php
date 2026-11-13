<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\UIAutoModeSwitchResponse;
use Revolution\Copilot\Types\Rpc\SessionUiRegisterDirectAutoModeSwitchHandlerRequest;
use Revolution\Copilot\Types\Rpc\SessionUiRegisterDirectAutoModeSwitchHandlerResult;
use Revolution\Copilot\Types\Rpc\UIExitPlanModeResponse;
use Revolution\Copilot\Types\Rpc\UIHandlePendingAutoModeSwitchRequest;
use Revolution\Copilot\Types\Rpc\UIHandlePendingExitPlanModeRequest;
use Revolution\Copilot\Types\Rpc\UIHandlePendingResult;
use Revolution\Copilot\Types\Rpc\UIHandlePendingSamplingRequest;
use Revolution\Copilot\Types\Rpc\UIHandlePendingUserInputRequest;
use Revolution\Copilot\Types\Rpc\UIUnregisterDirectAutoModeSwitchHandlerRequest;
use Revolution\Copilot\Types\Rpc\UIUnregisterDirectAutoModeSwitchHandlerResult;
use Revolution\Copilot\Types\Rpc\UIUserInputResponse;

describe('Pending UI RPC types', function () {
    it('round trips the shared result', function () {
        $result = UIHandlePendingResult::fromArray(['success' => true]);

        expect($result->success)->toBeTrue()
            ->and($result->toArray())->toBe(['success' => true]);
    });

    it('round trips a user input response and request', function () {
        $response = UIUserInputResponse::fromArray([
            'answer' => 'blue',
            'wasFreeform' => true,
        ]);
        $request = UIHandlePendingUserInputRequest::fromArray([
            'requestId' => 'user-1',
            'response' => $response->toArray(),
        ]);

        expect($response->toArray())->toBe(['answer' => 'blue', 'wasFreeform' => true])
            ->and($request->response)->toBeInstanceOf(UIUserInputResponse::class)
            ->and($request->toArray())->toBe([
                'requestId' => 'user-1',
                'response' => ['answer' => 'blue', 'wasFreeform' => true],
            ]);
    });

    it('round trips an exit plan mode response and request', function () {
        $response = UIExitPlanModeResponse::fromArray([
            'approved' => true,
            'selectedAction' => 'autopilot_fleet',
            'feedback' => 'Proceed',
        ]);
        $request = UIHandlePendingExitPlanModeRequest::fromArray([
            'requestId' => 'plan-1',
            'response' => $response->toArray(),
        ]);

        expect($response->toArray())->toBe([
            'approved' => true,
            'selectedAction' => 'autopilot_fleet',
            'feedback' => 'Proceed',
        ])
            ->and($request->response)->toBeInstanceOf(UIExitPlanModeResponse::class)
            ->and($request->toArray())->toBe([
                'requestId' => 'plan-1',
                'response' => [
                    'approved' => true,
                    'selectedAction' => 'autopilot_fleet',
                    'feedback' => 'Proceed',
                ],
            ]);
    });

    it('round trips an auto mode switch response and request', function () {
        $request = UIHandlePendingAutoModeSwitchRequest::fromArray([
            'requestId' => 'auto-1',
            'response' => UIAutoModeSwitchResponse::YesAlways->value,
        ]);

        expect($request->response)->toBe(UIAutoModeSwitchResponse::YesAlways)
            ->and($request->toArray())->toBe([
                'requestId' => 'auto-1',
                'response' => 'yes_always',
            ]);
    });

    it('round trips a sampling request with or without a response', function () {
        $request = UIHandlePendingSamplingRequest::fromArray([
            'requestId' => 'sampling-1',
            'response' => ['result' => 'sampled'],
        ]);
        $requestWithoutResponse = UIHandlePendingSamplingRequest::fromArray([
            'requestId' => 'sampling-2',
        ]);

        expect($request->toArray())->toBe([
            'requestId' => 'sampling-1',
            'response' => ['result' => 'sampled'],
        ])
            ->and($requestWithoutResponse->toArray())->toBe(['requestId' => 'sampling-2']);
    });

    it('round trips direct auto mode handler registration types', function () {
        $request = SessionUiRegisterDirectAutoModeSwitchHandlerRequest::fromArray([
            'autoModeRecovery' => false,
        ]);
        $requestWithoutOptions = SessionUiRegisterDirectAutoModeSwitchHandlerRequest::fromArray([]);
        $result = SessionUiRegisterDirectAutoModeSwitchHandlerResult::fromArray([
            'handle' => 'handler-123',
            'autoModeRecovery' => true,
        ]);
        $resultWithoutAcknowledgement = SessionUiRegisterDirectAutoModeSwitchHandlerResult::fromArray([
            'handle' => 'handler-456',
        ]);

        expect($request->autoModeRecovery)->toBeFalse()
            ->and($request->toArray())->toBe(['autoModeRecovery' => false])
            ->and($requestWithoutOptions->toArray())->toBe([])
            ->and($result->toArray())->toBe([
                'handle' => 'handler-123',
                'autoModeRecovery' => true,
            ])
            ->and($resultWithoutAcknowledgement->toArray())->toBe(['handle' => 'handler-456']);
    });

    it('round trips direct auto mode handler unregistration types', function () {
        $request = UIUnregisterDirectAutoModeSwitchHandlerRequest::fromArray([
            'handle' => 'handler-123',
        ]);
        $result = UIUnregisterDirectAutoModeSwitchHandlerResult::fromArray([
            'unregistered' => false,
        ]);

        expect($request->toArray())->toBe(['handle' => 'handler-123'])
            ->and($result->unregistered)->toBeFalse()
            ->and($result->toArray())->toBe(['unregistered' => false]);
    });
});
