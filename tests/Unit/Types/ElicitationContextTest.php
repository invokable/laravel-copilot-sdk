<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ElicitationContext;

describe('ElicitationContext', function () {
    it('can be created from array with all fields', function () {
        $context = ElicitationContext::fromArray([
            'sessionId' => 'session-123',
            'message' => 'Enter your API key',
            'requestedSchema' => ['type' => 'object', 'properties' => ['key' => ['type' => 'string']]],
            'mode' => 'form',
            'elicitationSource' => 'mcp-server',
            'url' => 'https://example.com/auth',
        ]);

        expect($context->sessionId)->toBe('session-123')
            ->and($context->message)->toBe('Enter your API key')
            ->and($context->requestedSchema)->toBe(['type' => 'object', 'properties' => ['key' => ['type' => 'string']]])
            ->and($context->mode)->toBe('form')
            ->and($context->elicitationSource)->toBe('mcp-server')
            ->and($context->url)->toBe('https://example.com/auth');
    });

    it('can be created from array with only required fields', function () {
        $context = ElicitationContext::fromArray([
            'sessionId' => 'session-456',
            'message' => 'Confirm action',
        ]);

        expect($context->sessionId)->toBe('session-456')
            ->and($context->message)->toBe('Confirm action')
            ->and($context->requestedSchema)->toBeNull()
            ->and($context->mode)->toBeNull()
            ->and($context->elicitationSource)->toBeNull()
            ->and($context->url)->toBeNull();
    });

    it('defaults sessionId and message to empty string when missing', function () {
        $context = ElicitationContext::fromArray([]);

        expect($context->sessionId)->toBe('')
            ->and($context->message)->toBe('');
    });

    it('converts to array filtering null values', function () {
        $context = ElicitationContext::fromArray([
            'sessionId' => 'session-789',
            'message' => 'Enter name',
            'mode' => 'form',
        ]);

        $array = $context->toArray();

        expect($array)->toHaveKey('sessionId', 'session-789')
            ->and($array)->toHaveKey('message', 'Enter name')
            ->and($array)->toHaveKey('mode', 'form')
            ->and($array)->not->toHaveKey('requestedSchema')
            ->and($array)->not->toHaveKey('elicitationSource')
            ->and($array)->not->toHaveKey('url');
    });

    it('converts to array with all fields populated', function () {
        $context = new ElicitationContext(
            sessionId: 'session-abc',
            message: 'Enter details',
            requestedSchema: ['type' => 'object'],
            mode: 'url',
            elicitationSource: 'test-server',
            url: 'https://example.com',
        );

        $array = $context->toArray();

        expect($array)->toBe([
            'sessionId' => 'session-abc',
            'message' => 'Enter details',
            'requestedSchema' => ['type' => 'object'],
            'mode' => 'url',
            'elicitationSource' => 'test-server',
            'url' => 'https://example.com',
        ]);
    });

    it('implements Arrayable', function () {
        $context = new ElicitationContext(sessionId: 'session-1', message: 'test');

        expect($context)->toBeInstanceOf(Arrayable::class);
    });
});
