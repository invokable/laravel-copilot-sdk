<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSandbox;
use Revolution\Copilot\Types\Rpc\SandboxCredentialSuggestionsResult;
use Revolution\Copilot\Types\Rpc\SandboxEnforcementStatus;

describe('PendingSandbox', function () {
    it('returns sandbox enforcement status', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.sandbox.getEnforcementStatus', ['sessionId' => 'session-abc'])
            ->andReturn([
                'required' => true,
                'blocked' => false,
            ]);

        $status = (new PendingSandbox($client, 'session-abc'))->getEnforcementStatus();

        expect($status)->toBeInstanceOf(SandboxEnforcementStatus::class)
            ->and($status->required)->toBeTrue()
            ->and($status->blocked)->toBeFalse()
            ->and($status->reason)->toBeNull();
    });

    it('returns credential suggestions for the session without exposing secret values', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.sandbox.getCredentialSuggestions', ['sessionId' => 'session-abc'])
            ->andReturn([
                'suggestions' => [
                    ['name' => 'API_TOKEN', 'suggestedInjectHosts' => ['api.example.com']],
                ],
            ]);

        $result = (new PendingSandbox($client, 'session-abc'))->getCredentialSuggestions();

        expect($result)->toBeInstanceOf(SandboxCredentialSuggestionsResult::class)
            ->and($result->suggestions)->toHaveCount(1)
            ->and($result->suggestions[0]->name)->toBe('API_TOKEN')
            ->and($result->suggestions[0]->suggestedInjectHosts)->toBe(['api.example.com']);
    });
});
