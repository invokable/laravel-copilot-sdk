<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ElicitationAction;
use Revolution\Copilot\Enums\UIAutoModeSwitchResponse;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingUi;
use Revolution\Copilot\Types\Rpc\UIElicitationRequest;
use Revolution\Copilot\Types\Rpc\UIElicitationResponse;
use Revolution\Copilot\Types\Rpc\UIElicitationResult;
use Revolution\Copilot\Types\Rpc\UIEphemeralQueryRequest;
use Revolution\Copilot\Types\Rpc\UIEphemeralQueryResult;
use Revolution\Copilot\Types\Rpc\UIExitPlanModeResponse;
use Revolution\Copilot\Types\Rpc\UIHandlePendingAutoModeSwitchRequest;
use Revolution\Copilot\Types\Rpc\UIHandlePendingElicitationRequest;
use Revolution\Copilot\Types\Rpc\UIHandlePendingExitPlanModeRequest;
use Revolution\Copilot\Types\Rpc\UIHandlePendingResult;
use Revolution\Copilot\Types\Rpc\UIHandlePendingSamplingRequest;
use Revolution\Copilot\Types\Rpc\UIHandlePendingUserInputRequest;
use Revolution\Copilot\Types\Rpc\UIUserInputResponse;

describe('PendingUi', function () {
    it('calls session.ui.ephemeralQuery with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.ui.ephemeralQuery',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['question'] === 'What is the answer?'),
            )
            ->andReturn(['answer' => '42']);

        $pending = new PendingUi($client, 'session-abc');
        $result = $pending->ephemeralQuery(new UIEphemeralQueryRequest(
            question: 'What is the answer?',
        ));

        expect($result)->toBeInstanceOf(UIEphemeralQueryResult::class)
            ->and($result->answer)->toBe('42');
    });

    it('calls session.ui.ephemeralQuery with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.ui.ephemeralQuery',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['question'] === 'What is the current context?'),
            )
            ->andReturn(['answer' => 'The current conversation context.']);

        $pending = new PendingUi($client, 'session-abc');
        $result = $pending->ephemeralQuery([
            'question' => 'What is the current context?',
        ]);

        expect($result)->toBeInstanceOf(UIEphemeralQueryResult::class)
            ->and($result->answer)->toBe('The current conversation context.');
    });

    it('calls session.ui.elicitation with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.ui.elicitation',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['message'] === 'Enter your name'
                    && $params['requestedSchema'] === ['type' => 'object']),
            )
            ->andReturn(['action' => 'accept', 'content' => ['name' => 'John']]);

        $pending = new PendingUi($client, 'session-abc');
        $result = $pending->elicitation(new UIElicitationRequest(
            message: 'Enter your name',
            requestedSchema: ['type' => 'object'],
        ));

        expect($result)->toBeInstanceOf(UIElicitationResponse::class)
            ->and($result->action)->toBe(ElicitationAction::ACCEPT)
            ->and($result->content)->toBe(['name' => 'John']);
    });

    it('calls session.ui.elicitation with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.ui.elicitation',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['message'] === 'Confirm action'),
            )
            ->andReturn(['action' => 'decline']);

        $pending = new PendingUi($client, 'session-abc');
        $result = $pending->elicitation([
            'message' => 'Confirm action',
            'requestedSchema' => ['type' => 'boolean'],
        ]);

        expect($result)->toBeInstanceOf(UIElicitationResponse::class)
            ->and($result->action)->toBe(ElicitationAction::DECLINE)
            ->and($result->content)->toBeNull();
    });

    it('handles cancel action', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.ui.elicitation',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'),
            )
            ->andReturn(['action' => 'cancel']);

        $pending = new PendingUi($client, 'session-abc');
        $result = $pending->elicitation(new UIElicitationRequest(
            message: 'Choose option',
            requestedSchema: ['type' => 'string'],
        ));

        expect($result)->toBeInstanceOf(UIElicitationResponse::class)
            ->and($result->action)->toBe(ElicitationAction::CANCEL)
            ->and($result->content)->toBeNull();
    });

    it('calls session.ui.handlePendingElicitation with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.ui.handlePendingElicitation',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['requestId'] === 'req-123'
                    && $params['result']['action'] === 'accept'
                    && $params['result']['content']['name'] === 'John'),
            )
            ->andReturn(['success' => true]);

        $pending = new PendingUi($client, 'session-abc');
        $result = $pending->handlePendingElicitation(new UIHandlePendingElicitationRequest(
            requestId: 'req-123',
            result: ['action' => 'accept', 'content' => ['name' => 'John']],
        ));

        expect($result)->toBeInstanceOf(UIElicitationResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('calls session.ui.handlePendingElicitation with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.ui.handlePendingElicitation',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['requestId'] === 'req-456'
                    && $params['result']['action'] === 'cancel'),
            )
            ->andReturn(['success' => false]);

        $pending = new PendingUi($client, 'session-abc');
        $result = $pending->handlePendingElicitation([
            'requestId' => 'req-456',
            'result' => ['action' => 'cancel'],
        ]);

        expect($result)->toBeInstanceOf(UIElicitationResult::class)
            ->and($result->success)->toBeFalse();
    });

    it('calls session.ui.handlePendingUserInput with typed and array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with(
                'session.ui.handlePendingUserInput',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['requestId'] === 'user-123'
                    && $params['response'] === ['answer' => 'blue', 'wasFreeform' => true]),
            )
            ->andReturn(['success' => true], ['success' => false]);

        $pending = new PendingUi($client, 'session-abc');
        $typedResult = $pending->handlePendingUserInput(new UIHandlePendingUserInputRequest(
            requestId: 'user-123',
            response: new UIUserInputResponse(answer: 'blue', wasFreeform: true),
        ));
        $arrayResult = $pending->handlePendingUserInput([
            'requestId' => 'user-123',
            'response' => ['answer' => 'blue', 'wasFreeform' => true],
        ]);

        expect($typedResult)->toBeInstanceOf(UIHandlePendingResult::class)
            ->and($typedResult->success)->toBeTrue()
            ->and($arrayResult->success)->toBeFalse();
    });

    it('calls session.ui.handlePendingExitPlanMode with typed and array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with(
                'session.ui.handlePendingExitPlanMode',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['requestId'] === 'plan-123'
                    && $params['response'] === [
                        'approved' => true,
                        'selectedAction' => 'autopilot_fleet',
                        'feedback' => 'Proceed',
                    ]),
            )
            ->andReturn(['success' => true], ['success' => false]);

        $pending = new PendingUi($client, 'session-abc');
        $typedResult = $pending->handlePendingExitPlanMode(new UIHandlePendingExitPlanModeRequest(
            requestId: 'plan-123',
            response: new UIExitPlanModeResponse(
                approved: true,
                selectedAction: 'autopilot_fleet',
                feedback: 'Proceed',
            ),
        ));
        $arrayResult = $pending->handlePendingExitPlanMode([
            'requestId' => 'plan-123',
            'response' => [
                'approved' => true,
                'selectedAction' => 'autopilot_fleet',
                'feedback' => 'Proceed',
            ],
        ]);

        expect($typedResult->success)->toBeTrue()
            ->and($arrayResult->success)->toBeFalse();
    });

    it('calls session.ui.handlePendingAutoModeSwitch with typed and array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with(
                'session.ui.handlePendingAutoModeSwitch',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['requestId'] === 'auto-123'
                    && $params['response'] === 'yes_always'),
            )
            ->andReturn(['success' => true], ['success' => false]);

        $pending = new PendingUi($client, 'session-abc');
        $typedResult = $pending->handlePendingAutoModeSwitch(new UIHandlePendingAutoModeSwitchRequest(
            requestId: 'auto-123',
            response: UIAutoModeSwitchResponse::YesAlways,
        ));
        $arrayResult = $pending->handlePendingAutoModeSwitch([
            'requestId' => 'auto-123',
            'response' => 'yes_always',
        ]);

        expect($typedResult->success)->toBeTrue()
            ->and($arrayResult->success)->toBeFalse();
    });

    it('calls session.ui.handlePendingSampling with typed and array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with(
                'session.ui.handlePendingSampling',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['requestId'] === 'sampling-123'
                    && ($params['response'] ?? null) === ['result' => 'sampled']),
            )
            ->andReturn(['success' => true], ['success' => false]);

        $pending = new PendingUi($client, 'session-abc');
        $typedResult = $pending->handlePendingSampling(new UIHandlePendingSamplingRequest(
            requestId: 'sampling-123',
            response: ['result' => 'sampled'],
        ));
        $arrayResult = $pending->handlePendingSampling([
            'requestId' => 'sampling-123',
            'response' => ['result' => 'sampled'],
        ]);

        expect($typedResult->success)->toBeTrue()
            ->and($arrayResult->success)->toBeFalse();
    });
});
