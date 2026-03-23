<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Session;
use Revolution\Copilot\Types\SessionCapabilities;

describe('HasUiApi', function () {
    it('returns empty capabilities by default', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $caps = $session->capabilities();

        expect($caps)->toBeInstanceOf(SessionCapabilities::class)
            ->and($caps->supportsElicitation())->toBeFalse();
    });

    it('can set capabilities from array', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $session->setCapabilities(['ui' => ['elicitation' => true]]);

        expect($session->capabilities()->supportsElicitation())->toBeTrue();
    });

    it('can set null capabilities', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $session->setCapabilities(null);

        expect($session->capabilities()->supportsElicitation())->toBeFalse();
    });

    it('throws RuntimeException when elicitation is not supported', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        expect(fn () => $session->confirm('Deploy?'))
            ->toThrow(RuntimeException::class, 'Elicitation is not supported by the host');
    });

    it('throws RuntimeException for select when not supported', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        expect(fn () => $session->select('Pick', ['a', 'b']))
            ->toThrow(RuntimeException::class, 'Elicitation is not supported by the host');
    });

    it('throws RuntimeException for input when not supported', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        expect(fn () => $session->input('Name?'))
            ->toThrow(RuntimeException::class, 'Elicitation is not supported by the host');
    });

    it('throws RuntimeException for raw elicitation when not supported', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        expect(fn () => $session->elicitation('msg', ['type' => 'object']))
            ->toThrow(RuntimeException::class, 'Elicitation is not supported by the host');
    });
});
