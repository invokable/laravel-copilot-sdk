<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\OptionsUpdateToolFilterPrecedence;
use Revolution\Copilot\Types\Rpc\McpRegistryCancelRequest;
use Revolution\Copilot\Types\Rpc\McpRegistryCancelResult;
use Revolution\Copilot\Types\Rpc\McpRegistryRequestIdResult;
use Revolution\Copilot\Types\Rpc\McpRegistrySearchRequest;
use Revolution\Copilot\Types\Rpc\McpRegistrySearchResult;
use Revolution\Copilot\Types\Rpc\McpShouldExcludeGitHubToolsRequest;
use Revolution\Copilot\Types\Rpc\McpShouldExcludeGitHubToolsResult;
use Revolution\Copilot\Types\Rpc\SandboxCredentialSuggestion;
use Revolution\Copilot\Types\Rpc\SandboxCredentialSuggestionsResult;

describe('McpRegistryCancelRequest', function () {
    it('round trips the request ID', function () {
        $request = McpRegistryCancelRequest::fromArray(['requestId' => 12]);

        expect($request->requestId)->toBe(12)
            ->and($request->toArray())->toBe(['requestId' => 12]);
    });
});

describe('McpRegistryCancelResult', function () {
    it('round trips whether a running search was canceled', function () {
        $result = McpRegistryCancelResult::fromArray(['canceled' => true]);

        expect($result->canceled)->toBeTrue()
            ->and($result->toArray())->toBe(['canceled' => true]);
    });
});

describe('McpRegistryRequestIdResult', function () {
    it('round trips an allocated request ID', function () {
        $result = McpRegistryRequestIdResult::fromArray(['requestId' => 23]);

        expect($result->requestId)->toBe(23)
            ->and($result->toArray())->toBe(['requestId' => 23]);
    });
});

describe('McpRegistrySearchRequest', function () {
    it('preserves opaque auth data and optional search fields', function () {
        $authInfo = ['kind' => 'gh-cli', 'metadata' => ['source' => 'host']];
        $request = McpRegistrySearchRequest::fromArray([
            'requestId' => 31,
            'authInfo' => $authInfo,
            'query' => 'database',
            'repository' => 'owner/project',
            'limit' => 15,
        ]);

        expect($request->authInfo)->toBe($authInfo)
            ->and($request->toArray())->toBe([
                'requestId' => 31,
                'authInfo' => $authInfo,
                'query' => 'database',
                'repository' => 'owner/project',
                'limit' => 15,
            ]);
    });

    it('omits unset optional search fields', function () {
        $request = new McpRegistrySearchRequest(
            requestId: 32,
            authInfo: [],
            limit: 5,
        );

        expect($request->toArray())->toBe([
            'requestId' => 32,
            'authInfo' => [],
            'limit' => 5,
        ]);
    });

    it('implements Arrayable', function () {
        expect(new McpRegistrySearchRequest(requestId: 1, authInfo: [], limit: 1))
            ->toBeInstanceOf(Arrayable::class);
    });
});

describe('McpRegistrySearchResult', function () {
    it('preserves opaque registry server data', function () {
        $servers = [
            ['name' => 'docs', 'metadata' => ['stars' => 100]],
        ];
        $result = McpRegistrySearchResult::fromArray(['servers' => $servers]);

        expect($result->servers)->toBe($servers)
            ->and($result->toArray())->toBe(['servers' => $servers]);
    });
});

describe('McpShouldExcludeGitHubToolsRequest', function () {
    it('maps tool filters and precedence', function () {
        $request = McpShouldExcludeGitHubToolsRequest::fromArray([
            'availableTools' => ['builtin:bash'],
            'excludedTools' => ['builtin:write'],
            'toolFilterPrecedence' => 'excluded',
        ]);

        expect($request->toolFilterPrecedence)->toBe(OptionsUpdateToolFilterPrecedence::Excluded)
            ->and($request->toArray())->toBe([
                'availableTools' => ['builtin:bash'],
                'excludedTools' => ['builtin:write'],
                'toolFilterPrecedence' => 'excluded',
            ]);
    });

    it('omits unset tool filters', function () {
        expect((new McpShouldExcludeGitHubToolsRequest)->toArray())->toBe([]);
    });
});

describe('McpShouldExcludeGitHubToolsResult', function () {
    it('round trips the exclusion decision', function () {
        $result = McpShouldExcludeGitHubToolsResult::fromArray([
            'excludeGhReplaceableTools' => true,
        ]);

        expect($result->excludeGhReplaceableTools)->toBeTrue()
            ->and($result->toArray())->toBe(['excludeGhReplaceableTools' => true]);
    });
});

describe('SandboxCredentialSuggestion', function () {
    it('maps a candidate without exposing a secret value', function () {
        $suggestion = SandboxCredentialSuggestion::fromArray([
            'name' => 'API_TOKEN',
            'suggestedInjectHosts' => ['api.example.com'],
        ]);

        expect($suggestion->name)->toBe('API_TOKEN')
            ->and($suggestion->suggestedInjectHosts)->toBe(['api.example.com'])
            ->and($suggestion->toArray())->toBe([
                'name' => 'API_TOKEN',
                'suggestedInjectHosts' => ['api.example.com'],
            ]);
    });
});

describe('SandboxCredentialSuggestionsResult', function () {
    it('converts nested suggestions to typed objects and back', function () {
        $result = SandboxCredentialSuggestionsResult::fromArray([
            'suggestions' => [
                ['name' => 'API_TOKEN', 'suggestedInjectHosts' => ['api.example.com']],
                ['name' => 'CUSTOM_SECRET', 'suggestedInjectHosts' => []],
            ],
        ]);

        expect($result->suggestions)->toHaveCount(2)
            ->and($result->suggestions[0])->toBeInstanceOf(SandboxCredentialSuggestion::class)
            ->and($result->suggestions[0]->name)->toBe('API_TOKEN')
            ->and($result->suggestions[1]->suggestedInjectHosts)->toBe([])
            ->and($result->toArray())->toBe([
                'suggestions' => [
                    ['name' => 'API_TOKEN', 'suggestedInjectHosts' => ['api.example.com']],
                    ['name' => 'CUSTOM_SECRET', 'suggestedInjectHosts' => []],
                ],
            ]);
    });

    it('defaults missing suggestions to an empty list', function () {
        expect(SandboxCredentialSuggestionsResult::fromArray([])->suggestions)->toBe([]);
    });
});
