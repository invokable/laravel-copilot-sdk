<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\NetworkScope;
use Revolution\Copilot\Types\Rpc\NetworkAuthenticateNegotiateRequest;
use Revolution\Copilot\Types\Rpc\NetworkAuthenticateNegotiateResult;
use Revolution\Copilot\Types\Rpc\NetworkResolveProxyRequest;
use Revolution\Copilot\Types\Rpc\NetworkResolveProxyResult;
use Revolution\Copilot\Types\Rpc\NetworkSetProviderRequest;

describe('NetworkSetProviderRequest', function () {
    it('round-trips scope and registered callback markers as JSON objects', function () {
        $request = NetworkSetProviderRequest::fromArray([
            'scope' => 'runtime',
            'resolveProxy' => [],
            'authenticateNegotiate' => [],
        ]);

        expect($request->scope)->toBe(NetworkScope::Runtime)
            ->and($request->resolveProxy)->toBeTrue()
            ->and($request->authenticateNegotiate)->toBeTrue()
            ->and($request->toArray())->toEqual([
                'scope' => 'runtime',
                'resolveProxy' => (object) [],
                'authenticateNegotiate' => (object) [],
            ])
            ->and(json_encode($request->toArray()))->toBe(
                '{"scope":"runtime","resolveProxy":{},"authenticateNegotiate":{}}',
            );
    });

    it('omits absent scope and callback markers', function () {
        $request = new NetworkSetProviderRequest;

        expect($request->toArray())->toBe([]);
    });
});

describe('NetworkResolveProxyRequest', function () {
    it('round-trips the request URL', function () {
        $request = NetworkResolveProxyRequest::fromArray(['url' => 'https://api.example/']);

        expect($request->url)->toBe('https://api.example/')
            ->and($request->toArray())->toBe(['url' => 'https://api.example/']);
    });
});

describe('NetworkResolveProxyResult', function () {
    it('round-trips the proxy list', function () {
        $result = NetworkResolveProxyResult::fromArray([
            'proxy' => 'PROXY proxy.example:8080; DIRECT',
        ]);

        expect($result->proxy)->toBe('PROXY proxy.example:8080; DIRECT')
            ->and($result->toArray())->toBe([
                'proxy' => 'PROXY proxy.example:8080; DIRECT',
            ]);
    });

    it('omits an unavailable proxy list', function () {
        expect((new NetworkResolveProxyResult)->toArray())->toBe([]);
    });
});

describe('NetworkAuthenticateNegotiateRequest', function () {
    it('round-trips the service principal name', function () {
        $request = NetworkAuthenticateNegotiateRequest::fromArray([
            'spn' => 'HTTP/proxy.example@EXAMPLE.COM',
        ]);

        expect($request->spn)->toBe('HTTP/proxy.example@EXAMPLE.COM')
            ->and($request->toArray())->toBe(['spn' => 'HTTP/proxy.example@EXAMPLE.COM']);
    });
});

describe('NetworkAuthenticateNegotiateResult', function () {
    it('round-trips the acquired token', function () {
        $result = NetworkAuthenticateNegotiateResult::fromArray(['token' => 'spnego-token']);

        expect($result->token)->toBe('spnego-token')
            ->and($result->toArray())->toBe(['token' => 'spnego-token']);
    });

    it('omits an unavailable token', function () {
        expect((new NetworkAuthenticateNegotiateResult)->toArray())->toBe([]);
    });
});
