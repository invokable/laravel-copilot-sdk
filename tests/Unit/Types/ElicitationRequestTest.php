<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ElicitationRequest;

describe('ElicitationRequest', function () {
    it('can be created from array with all fields', function () {
        $request = ElicitationRequest::fromArray([
            'message' => 'Enter your API key',
            'requestedSchema' => ['type' => 'object', 'properties' => ['key' => ['type' => 'string']]],
            'mode' => 'form',
            'elicitationSource' => 'mcp-server',
            'url' => 'https://example.com/auth',
        ]);

        expect($request->message)->toBe('Enter your API key')
            ->and($request->requestedSchema)->toBe(['type' => 'object', 'properties' => ['key' => ['type' => 'string']]])
            ->and($request->mode)->toBe('form')
            ->and($request->elicitationSource)->toBe('mcp-server')
            ->and($request->url)->toBe('https://example.com/auth');
    });

    it('can be created from array with only required fields', function () {
        $request = ElicitationRequest::fromArray([
            'message' => 'Confirm action',
        ]);

        expect($request->message)->toBe('Confirm action')
            ->and($request->requestedSchema)->toBeNull()
            ->and($request->mode)->toBeNull()
            ->and($request->elicitationSource)->toBeNull()
            ->and($request->url)->toBeNull();
    });

    it('converts to array filtering null values', function () {
        $request = ElicitationRequest::fromArray([
            'message' => 'Enter name',
            'mode' => 'form',
        ]);

        $array = $request->toArray();

        expect($array)->toHaveKey('message', 'Enter name')
            ->and($array)->toHaveKey('mode', 'form')
            ->and($array)->not->toHaveKey('requestedSchema')
            ->and($array)->not->toHaveKey('elicitationSource')
            ->and($array)->not->toHaveKey('url');
    });

    it('converts to array with all fields populated', function () {
        $request = new ElicitationRequest(
            message: 'Enter details',
            requestedSchema: ['type' => 'object'],
            mode: 'url',
            elicitationSource: 'test-server',
            url: 'https://example.com',
        );

        $array = $request->toArray();

        expect($array)->toBe([
            'message' => 'Enter details',
            'requestedSchema' => ['type' => 'object'],
            'mode' => 'url',
            'elicitationSource' => 'test-server',
            'url' => 'https://example.com',
        ]);
    });

    it('implements Arrayable', function () {
        $request = new ElicitationRequest(message: 'test');

        expect($request)->toBeInstanceOf(Arrayable::class);
    });
});
