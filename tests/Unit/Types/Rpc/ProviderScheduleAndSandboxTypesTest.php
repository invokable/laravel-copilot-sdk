<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpOauthLoginGrantType;
use Revolution\Copilot\Enums\ProviderConfigTransport;
use Revolution\Copilot\Enums\ProviderEndpointTransport;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Types\NamedProviderConfig;
use Revolution\Copilot\Types\ProviderConfig;
use Revolution\Copilot\Types\Rpc\McpOauthLoginRequest;
use Revolution\Copilot\Types\Rpc\ProviderEndpoint;
use Revolution\Copilot\Types\Rpc\ProviderTokenAcquireRequest;
use Revolution\Copilot\Types\Rpc\ProviderTokenAcquireResult;
use Revolution\Copilot\Types\Rpc\SandboxConfigUserPolicySeatbelt;
use Revolution\Copilot\Types\Rpc\ScheduleEntry;

describe('McpOauthLoginGrantType', function () {
    it('has correct string values', function () {
        expect(McpOauthLoginGrantType::AuthorizationCode->value)->toBe('authorization_code')
            ->and(McpOauthLoginGrantType::ClientCredentials->value)->toBe('client_credentials');
    });

    it('can be created from string', function () {
        expect(McpOauthLoginGrantType::from('authorization_code'))->toBe(McpOauthLoginGrantType::AuthorizationCode)
            ->and(McpOauthLoginGrantType::from('client_credentials'))->toBe(McpOauthLoginGrantType::ClientCredentials);
    });

    it('has all expected cases', function () {
        expect(McpOauthLoginGrantType::cases())->toHaveCount(2);
    });
});

describe('ProviderConfigTransport', function () {
    it('has correct string values', function () {
        expect(ProviderConfigTransport::Http->value)->toBe('http')
            ->and(ProviderConfigTransport::Websockets->value)->toBe('websockets');
    });

    it('can be created from string', function () {
        expect(ProviderConfigTransport::from('http'))->toBe(ProviderConfigTransport::Http)
            ->and(ProviderConfigTransport::from('websockets'))->toBe(ProviderConfigTransport::Websockets);
    });
});

describe('ProviderEndpointTransport', function () {
    it('has correct string values', function () {
        expect(ProviderEndpointTransport::Http->value)->toBe('http')
            ->and(ProviderEndpointTransport::Websockets->value)->toBe('websockets');
    });

    it('can be created from string', function () {
        expect(ProviderEndpointTransport::from('websockets'))->toBe(ProviderEndpointTransport::Websockets);
    });
});

describe('SessionEventType new canvas and schedule events', function () {
    it('has session.schedule_rearmed event type', function () {
        expect(SessionEventType::SESSION_SCHEDULE_REARMED->value)->toBe('session.schedule_rearmed');
    });

    it('has session.canvas.unavailable event type', function () {
        expect(SessionEventType::SESSION_CANVAS_UNAVAILABLE->value)->toBe('session.canvas.unavailable');
    });

    it('has session.canvas.recorded event type', function () {
        expect(SessionEventType::SESSION_CANVAS_RECORDED->value)->toBe('session.canvas.recorded');
    });

    it('has session.canvas.removed event type', function () {
        expect(SessionEventType::SESSION_CANVAS_REMOVED->value)->toBe('session.canvas.removed');
    });
});

describe('McpOauthLoginRequest with new fields', function () {
    it('can be created with new OAuth client fields', function () {
        $request = new McpOauthLoginRequest(
            serverName: 'my-mcp-server',
            clientId: 'client-abc',
            clientSecret: 'secret-xyz',
            publicClient: false,
            grantType: McpOauthLoginGrantType::AuthorizationCode,
        );

        expect($request->serverName)->toBe('my-mcp-server')
            ->and($request->clientId)->toBe('client-abc')
            ->and($request->clientSecret)->toBe('secret-xyz')
            ->and($request->publicClient)->toBeFalse()
            ->and($request->grantType)->toBe(McpOauthLoginGrantType::AuthorizationCode);
    });

    it('can be created from array with grant type string', function () {
        $request = McpOauthLoginRequest::fromArray([
            'serverName' => 'test-server',
            'grantType' => 'client_credentials',
            'clientId' => 'my-client',
        ]);

        expect($request->grantType)->toBe(McpOauthLoginGrantType::ClientCredentials)
            ->and($request->clientId)->toBe('my-client');
    });

    it('converts to array including new fields', function () {
        $request = new McpOauthLoginRequest(
            serverName: 'test-server',
            clientId: 'cid',
            grantType: McpOauthLoginGrantType::ClientCredentials,
        );

        $array = $request->toArray();

        expect($array)->toHaveKey('serverName', 'test-server')
            ->and($array)->toHaveKey('clientId', 'cid')
            ->and($array)->toHaveKey('grantType', 'client_credentials')
            ->and($array)->not->toHaveKey('clientSecret');
    });

    it('excludes null new fields from array', function () {
        $request = new McpOauthLoginRequest(serverName: 'server');

        expect($request->toArray())->toBe(['serverName' => 'server']);
    });
});

describe('ScheduleEntry selfPaced field', function () {
    it('can be created with selfPaced true', function () {
        $entry = ScheduleEntry::fromArray([
            'id' => 1,
            'intervalMs' => 0,
            'nextRunAt' => '2024-01-01T01:00:00Z',
            'prompt' => 'wakeup',
            'recurring' => true,
            'selfPaced' => true,
        ]);

        expect($entry->selfPaced)->toBeTrue();
    });

    it('selfPaced defaults to null', function () {
        $entry = ScheduleEntry::fromArray([
            'id' => 1,
            'intervalMs' => 60000,
            'nextRunAt' => '2024-01-01T01:00:00Z',
            'prompt' => 'test',
            'recurring' => false,
        ]);

        expect($entry->selfPaced)->toBeNull();
    });

    it('converts selfPaced to array when set', function () {
        $entry = new ScheduleEntry(
            id: 1,
            intervalMs: 0,
            nextRunAt: '',
            prompt: 'p',
            recurring: true,
            selfPaced: true,
        );

        expect($entry->toArray())->toHaveKey('selfPaced', true);
    });

    it('excludes selfPaced from array when null', function () {
        $entry = new ScheduleEntry(id: 1, intervalMs: 0, nextRunAt: '', prompt: 'p', recurring: false);

        expect($entry->toArray())->not->toHaveKey('selfPaced');
    });
});

describe('NamedProviderConfig with transport and hasBearerTokenProvider', function () {
    it('can be created with transport', function () {
        $config = NamedProviderConfig::fromArray([
            'name' => 'my-provider',
            'baseUrl' => 'https://api.example.com',
            'transport' => 'websockets',
        ]);

        expect($config->transport)->toBe('websockets');
    });

    it('can be created with hasBearerTokenProvider', function () {
        $config = NamedProviderConfig::fromArray([
            'name' => 'azure-provider',
            'baseUrl' => 'https://azure.example.com',
            'hasBearerTokenProvider' => true,
        ]);

        expect($config->hasBearerTokenProvider)->toBeTrue();
    });

    it('converts new fields to array', function () {
        $config = new NamedProviderConfig(
            name: 'byok',
            baseUrl: 'https://api.example.com',
            transport: 'http',
            hasBearerTokenProvider: true,
        );

        $array = $config->toArray();

        expect($array)->toHaveKey('transport', 'http')
            ->and($array)->toHaveKey('hasBearerTokenProvider', true);
    });
});

describe('ProviderConfig hasBearerTokenProvider field', function () {
    it('can be created with hasBearerTokenProvider', function () {
        $config = ProviderConfig::fromArray([
            'baseUrl' => 'https://api.example.com',
            'hasBearerTokenProvider' => true,
        ]);

        expect($config->hasBearerTokenProvider)->toBeTrue();
    });

    it('hasBearerTokenProvider defaults to null', function () {
        $config = ProviderConfig::fromArray(['baseUrl' => 'https://api.example.com']);

        expect($config->hasBearerTokenProvider)->toBeNull();
    });

    it('converts hasBearerTokenProvider to array when set', function () {
        $config = new ProviderConfig(baseUrl: 'https://api.example.com', hasBearerTokenProvider: true);

        expect($config->toArray())->toHaveKey('hasBearerTokenProvider', true);
    });
});

describe('ProviderEndpoint transport field', function () {
    it('can be created with transport', function () {
        $endpoint = ProviderEndpoint::fromArray([
            'baseUrl' => 'https://api.example.com',
            'type' => 'openai',
            'headers' => [],
            'transport' => 'websockets',
        ]);

        expect($endpoint->transport)->toBe('websockets');
    });

    it('transport defaults to null', function () {
        $endpoint = ProviderEndpoint::fromArray([
            'baseUrl' => 'https://api.example.com',
            'type' => 'openai',
            'headers' => [],
        ]);

        expect($endpoint->transport)->toBeNull();
    });

    it('converts transport to array when set', function () {
        $endpoint = new ProviderEndpoint(
            baseUrl: 'https://api.example.com',
            type: 'openai',
            headers: [],
            transport: 'http',
        );

        expect($endpoint->toArray())->toHaveKey('transport', 'http');
    });
});

describe('ProviderTokenAcquireRequest', function () {
    it('can be created with required fields', function () {
        $request = new ProviderTokenAcquireRequest(
            sessionId: 'sess-123',
            providerName: 'azure-provider',
        );

        expect($request->sessionId)->toBe('sess-123')
            ->and($request->providerName)->toBe('azure-provider');
    });

    it('can be created from array', function () {
        $request = ProviderTokenAcquireRequest::fromArray([
            'sessionId' => 'sess-abc',
            'providerName' => 'my-provider',
        ]);

        expect($request->sessionId)->toBe('sess-abc')
            ->and($request->providerName)->toBe('my-provider');
    });

    it('converts to array', function () {
        $request = new ProviderTokenAcquireRequest(
            sessionId: 'sess-xyz',
            providerName: 'default',
        );

        expect($request->toArray())->toBe([
            'sessionId' => 'sess-xyz',
            'providerName' => 'default',
        ]);
    });

    it('implements Arrayable', function () {
        expect(new ProviderTokenAcquireRequest(sessionId: 's', providerName: 'p'))->toBeInstanceOf(Arrayable::class);
    });
});

describe('ProviderTokenAcquireResult', function () {
    it('can be created with token', function () {
        $result = new ProviderTokenAcquireResult(token: 'eyJhbGciOiJSUzI1NiJ9.test');

        expect($result->token)->toBe('eyJhbGciOiJSUzI1NiJ9.test');
    });

    it('can be created from array', function () {
        $result = ProviderTokenAcquireResult::fromArray(['token' => 'my-bearer-token']);

        expect($result->token)->toBe('my-bearer-token');
    });

    it('converts to array', function () {
        $result = new ProviderTokenAcquireResult(token: 'access-token-value');

        expect($result->toArray())->toBe(['token' => 'access-token-value']);
    });

    it('implements Arrayable', function () {
        expect(new ProviderTokenAcquireResult(token: 'tok'))->toBeInstanceOf(Arrayable::class);
    });
});

describe('SandboxConfigUserPolicySeatbelt', function () {
    it('can be created with keychainAccess', function () {
        $seatbelt = new SandboxConfigUserPolicySeatbelt(keychainAccess: true);

        expect($seatbelt->keychainAccess)->toBeTrue();
    });

    it('keychainAccess defaults to null', function () {
        $seatbelt = new SandboxConfigUserPolicySeatbelt;

        expect($seatbelt->keychainAccess)->toBeNull();
    });

    it('can be created from array', function () {
        $seatbelt = SandboxConfigUserPolicySeatbelt::fromArray(['keychainAccess' => false]);

        expect($seatbelt->keychainAccess)->toBeFalse();
    });

    it('converts to array omitting null', function () {
        $seatbelt = new SandboxConfigUserPolicySeatbelt;

        expect($seatbelt->toArray())->toBe([]);
    });

    it('converts keychainAccess to array when set', function () {
        $seatbelt = new SandboxConfigUserPolicySeatbelt(keychainAccess: true);

        expect($seatbelt->toArray())->toBe(['keychainAccess' => true]);
    });

    it('implements Arrayable', function () {
        expect(new SandboxConfigUserPolicySeatbelt)->toBeInstanceOf(Arrayable::class);
    });
});
