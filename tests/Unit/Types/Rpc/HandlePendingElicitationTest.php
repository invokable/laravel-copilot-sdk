<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\UIElicitationResult;
use Revolution\Copilot\Types\Rpc\UIHandlePendingElicitationRequest;

describe('UIHandlePendingElicitationRequest', function () {
    it('can be created from array', function () {
        $params = UIHandlePendingElicitationRequest::fromArray([
            'requestId' => 'req-123',
            'result' => ['action' => 'accept', 'content' => ['name' => 'John']],
        ]);

        expect($params->requestId)->toBe('req-123')
            ->and($params->result)->toBe(['action' => 'accept', 'content' => ['name' => 'John']]);
    });

    it('converts to array', function () {
        $params = new UIHandlePendingElicitationRequest(
            requestId: 'req-456',
            result: ['action' => 'decline'],
        );

        expect($params->toArray())->toBe([
            'requestId' => 'req-456',
            'result' => ['action' => 'decline'],
        ]);
    });

    it('supports cancel action', function () {
        $params = UIHandlePendingElicitationRequest::fromArray([
            'requestId' => 'req-789',
            'result' => ['action' => 'cancel'],
        ]);

        expect($params->result['action'])->toBe('cancel')
            ->and($params->result)->not->toHaveKey('content');
    });

    it('implements Arrayable', function () {
        $params = new UIHandlePendingElicitationRequest(
            requestId: 'req-1',
            result: ['action' => 'accept'],
        );

        expect($params)->toBeInstanceOf(Arrayable::class);
    });
});

describe('UIElicitationResult', function () {
    it('can be created from array with success true', function () {
        $result = UIElicitationResult::fromArray(['success' => true]);

        expect($result->success)->toBeTrue();
    });

    it('can be created from array with success false', function () {
        $result = UIElicitationResult::fromArray(['success' => false]);

        expect($result->success)->toBeFalse();
    });

    it('converts to array', function () {
        $result = new UIElicitationResult(success: true);

        expect($result->toArray())->toBe(['success' => true]);
    });

    it('implements Arrayable', function () {
        $result = new UIElicitationResult(success: true);

        expect($result)->toBeInstanceOf(Arrayable::class);
    });
});
