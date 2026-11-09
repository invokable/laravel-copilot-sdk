<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\AutoModeRecoveryContext;
use Revolution\Copilot\Types\AutoModeSwitchRequest;

describe('AutoModeSwitchRequest', function () {
    it('can be created from array with all fields', function () {
        $request = AutoModeSwitchRequest::fromArray([
            'errorCode' => 'RATE_LIMIT_EXCEEDED',
            'retryAfterSeconds' => 60,
        ]);

        expect($request->errorCode)->toBe('RATE_LIMIT_EXCEEDED')
            ->and($request->retryAfterSeconds)->toBe(60);
    });

    it('handles missing optional fields', function () {
        $request = AutoModeSwitchRequest::fromArray([]);

        expect($request->errorCode)->toBeNull()
            ->and($request->retryAfterSeconds)->toBeNull();
    });

    it('casts retryAfterSeconds to int', function () {
        $request = AutoModeSwitchRequest::fromArray([
            'retryAfterSeconds' => '30',
        ]);

        expect($request->retryAfterSeconds)->toBe(30);
    });

    it('converts to array filtering null values', function () {
        $request = AutoModeSwitchRequest::fromArray([
            'errorCode' => 'RATE_LIMIT_EXCEEDED',
        ]);

        $array = $request->toArray();

        expect($array)->toHaveKey('errorCode', 'RATE_LIMIT_EXCEEDED')
            ->and($array)->not->toHaveKey('retryAfterSeconds');
    });

    it('implements Arrayable', function () {
        expect(new AutoModeSwitchRequest)->toBeInstanceOf(Arrayable::class);
    });

    it('preserves auto mode recovery context', function () {
        $request = AutoModeSwitchRequest::fromArray([
            'autoModeRecovery' => [
                'requestId' => 'request-123',
                'originalModel' => 'gpt-4.1',
                'allowAlways' => false,
                'interactionId' => 'interaction-456',
                'originatingMessageId' => 'message-789',
            ],
        ]);

        expect($request->autoModeRecovery)->toBeInstanceOf(AutoModeRecoveryContext::class)
            ->and($request->autoModeRecovery)->toBeInstanceOf(Arrayable::class)
            ->and($request->autoModeRecovery->requestId)->toBe('request-123')
            ->and($request->autoModeRecovery->originalModel)->toBe('gpt-4.1')
            ->and($request->autoModeRecovery->allowAlways)->toBeFalse()
            ->and($request->autoModeRecovery->interactionId)->toBe('interaction-456')
            ->and($request->autoModeRecovery->originatingMessageId)->toBe('message-789')
            ->and($request->toArray()['autoModeRecovery'])->toBe([
                'requestId' => 'request-123',
                'originalModel' => 'gpt-4.1',
                'allowAlways' => false,
                'interactionId' => 'interaction-456',
                'originatingMessageId' => 'message-789',
            ]);
    });

    it('omits missing optional recovery context fields', function () {
        $request = AutoModeSwitchRequest::fromArray([
            'autoModeRecovery' => [
                'requestId' => 'request-123',
                'originalModel' => 'gpt-4.1',
                'allowAlways' => true,
            ],
        ]);

        expect($request->autoModeRecovery->toArray())->toBe([
            'requestId' => 'request-123',
            'originalModel' => 'gpt-4.1',
            'allowAlways' => true,
        ]);
    });
});
