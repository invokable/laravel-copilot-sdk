<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ElicitationAction;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingUi;
use Revolution\Copilot\Types\Rpc\SessionUiElicitationParams;
use Revolution\Copilot\Types\Rpc\SessionUiElicitationResult;

describe('PendingUi', function () {
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
        $result = $pending->elicitation(new SessionUiElicitationParams(
            message: 'Enter your name',
            requestedSchema: ['type' => 'object'],
        ));

        expect($result)->toBeInstanceOf(SessionUiElicitationResult::class)
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

        expect($result)->toBeInstanceOf(SessionUiElicitationResult::class)
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
        $result = $pending->elicitation(new SessionUiElicitationParams(
            message: 'Choose option',
            requestedSchema: ['type' => 'string'],
        ));

        expect($result)->toBeInstanceOf(SessionUiElicitationResult::class)
            ->and($result->action)->toBe(ElicitationAction::CANCEL)
            ->and($result->content)->toBeNull();
    });
});
