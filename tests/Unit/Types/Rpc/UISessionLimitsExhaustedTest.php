<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SessionLimitsExhaustedResponseAction;
use Revolution\Copilot\Types\Rpc\UIHandlePendingSessionLimitsExhaustedRequest;
use Revolution\Copilot\Types\Rpc\UISessionLimitsExhaustedResponse;

describe('UISessionLimitsExhaustedResponse', function () {
    it('can be created from array with cancel action', function () {
        $response = UISessionLimitsExhaustedResponse::fromArray(['action' => 'cancel']);

        expect($response->action)->toBe(SessionLimitsExhaustedResponseAction::Cancel)
            ->and($response->additionalAiCredits)->toBeNull()
            ->and($response->maxAiCredits)->toBeNull();
    });

    it('can be created from array with add action', function () {
        $response = UISessionLimitsExhaustedResponse::fromArray([
            'action' => 'add',
            'additionalAiCredits' => 50.0,
        ]);

        expect($response->action)->toBe(SessionLimitsExhaustedResponseAction::Add)
            ->and($response->additionalAiCredits)->toBe(50.0);
    });

    it('can be created from array with set action', function () {
        $response = UISessionLimitsExhaustedResponse::fromArray([
            'action' => 'set',
            'maxAiCredits' => 200.0,
        ]);

        expect($response->action)->toBe(SessionLimitsExhaustedResponseAction::Set)
            ->and($response->maxAiCredits)->toBe(200.0);
    });

    it('converts to array filtering nulls', function () {
        $response = new UISessionLimitsExhaustedResponse(
            action: SessionLimitsExhaustedResponseAction::Cancel,
        );

        expect($response->toArray())->toBe(['action' => 'cancel']);
    });

    it('converts to array with all fields', function () {
        $response = new UISessionLimitsExhaustedResponse(
            action: SessionLimitsExhaustedResponseAction::Add,
            additionalAiCredits: 25.0,
        );

        expect($response->toArray())->toBe([
            'action' => 'add',
            'additionalAiCredits' => 25.0,
        ]);
    });

    it('accepts string action', function () {
        $response = UISessionLimitsExhaustedResponse::fromArray(['action' => 'unset']);

        expect($response->action)->toBe(SessionLimitsExhaustedResponseAction::Unset);
    });

    it('implements Arrayable', function () {
        expect(new UISessionLimitsExhaustedResponse(action: 'cancel'))->toBeInstanceOf(Arrayable::class);
    });
});

describe('UIHandlePendingSessionLimitsExhaustedRequest', function () {
    it('can be created from array', function () {
        $request = UIHandlePendingSessionLimitsExhaustedRequest::fromArray([
            'requestId' => 'req-123',
            'response' => ['action' => 'cancel'],
        ]);

        expect($request->requestId)->toBe('req-123')
            ->and($request->response)->toBeInstanceOf(UISessionLimitsExhaustedResponse::class)
            ->and($request->response->action)->toBe(SessionLimitsExhaustedResponseAction::Cancel);
    });

    it('converts to array', function () {
        $response = new UISessionLimitsExhaustedResponse(action: SessionLimitsExhaustedResponseAction::Cancel);
        $request = new UIHandlePendingSessionLimitsExhaustedRequest(
            requestId: 'req-456',
            response: $response,
        );

        expect($request->toArray())->toBe([
            'requestId' => 'req-456',
            'response' => ['action' => 'cancel'],
        ]);
    });

    it('accepts array response', function () {
        $request = new UIHandlePendingSessionLimitsExhaustedRequest(
            requestId: 'req-789',
            response: ['action' => 'unset'],
        );

        expect($request->toArray()['response'])->toBe(['action' => 'unset']);
    });

    it('implements Arrayable', function () {
        $response = new UISessionLimitsExhaustedResponse(action: 'cancel');
        $request = new UIHandlePendingSessionLimitsExhaustedRequest(requestId: 'r', response: $response);

        expect($request)->toBeInstanceOf(Arrayable::class);
    });
});
