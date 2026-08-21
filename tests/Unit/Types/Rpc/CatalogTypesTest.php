<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogCapability;
use Revolution\Copilot\Types\Rpc\CardDigest;
use Revolution\Copilot\Types\Rpc\CatalogAiSkillCandidate;
use Revolution\Copilot\Types\Rpc\CatalogAiSkillCandidateProvenance;
use Revolution\Copilot\Types\Rpc\CatalogCandidateSourceEmbedded;
use Revolution\Copilot\Types\Rpc\CatalogCandidateSourceUrl;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\CatalogMcpServerCandidate;
use Revolution\Copilot\Types\Rpc\CatalogMcpServerCandidateProvenance;
use Revolution\Copilot\Types\Rpc\CatalogNegotiatedContract;
use Revolution\Copilot\Types\Rpc\CatalogSearchRequest;
use Revolution\Copilot\Types\Rpc\CatalogSearchSucceeded;
use Revolution\Copilot\Types\Rpc\CatalogUnsupportedKindError;

describe('CatalogClientContract', function () {
    it('can be created from array', function () {
        $contract = CatalogClientContract::fromArray([
            'protocolVersion' => 1,
            'requiredCapabilities' => ['mcp-server-card'],
        ]);

        expect($contract->protocolVersion)->toBe(1)
            ->and($contract->requiredCapabilities)->toBe(['mcp-server-card']);
    });

    it('round-trips via toArray', function () {
        $contract = new CatalogClientContract(2, ['mcp-server-card', 'ai-skill-discovery']);
        $arr = $contract->toArray();

        expect($arr['protocolVersion'])->toBe(2)
            ->and($arr['requiredCapabilities'])->toBe(['mcp-server-card', 'ai-skill-discovery']);
    });
});

describe('CatalogNegotiatedContract', function () {
    it('can be created from array', function () {
        $nc = CatalogNegotiatedContract::fromArray([
            'runtimeProtocolVersion' => 1,
            'grantedCapabilities' => ['mcp-server-card'],
        ]);

        expect($nc->runtimeProtocolVersion)->toBe(1)
            ->and($nc->grantedCapabilities)->toHaveCount(1)
            ->and($nc->grantedCapabilities[0])->toBe(CatalogCapability::McpServerCard);
    });

    it('round-trips via toArray', function () {
        $nc = new CatalogNegotiatedContract(1, [CatalogCapability::McpServerCard]);
        $arr = $nc->toArray();

        expect($arr['grantedCapabilities'][0])->toBe('mcp-server-card');
    });
});

describe('CatalogSearchRequest', function () {
    it('can be created from array', function () {
        $req = CatalogSearchRequest::fromArray([
            'contract' => ['protocolVersion' => 1, 'requiredCapabilities' => []],
            'query' => 'github',
            'limit' => 10,
        ]);

        expect($req->query)->toBe('github')
            ->and($req->limit)->toBe(10)
            ->and($req->kinds)->toBeNull();
    });

    it('omits null optional fields in toArray', function () {
        $req = new CatalogSearchRequest(
            contract: new CatalogClientContract(1, []),
            query: 'stripe',
        );
        $arr = $req->toArray();

        expect($arr)->not->toHaveKey('limit')
            ->and($arr)->not->toHaveKey('kinds');
    });
});

describe('CatalogCandidateSource', function () {
    it('creates CatalogCandidateSourceUrl', function () {
        $src = CatalogCandidateSourceUrl::fromArray(['kind' => 'url', 'url' => 'https://example.com/card.json']);

        expect($src->url)->toBe('https://example.com/card.json')
            ->and($src->toArray()['kind'])->toBe('url');
    });

    it('creates CatalogCandidateSourceEmbedded', function () {
        $src = CatalogCandidateSourceEmbedded::fromArray(['kind' => 'embedded']);

        expect($src->toArray()['kind'])->toBe('embedded');
    });
});

describe('CatalogMcpServerCandidate', function () {
    it('can be created from array', function () {
        $candidate = CatalogMcpServerCandidate::fromArray([
            'kind' => 'mcp-server',
            'handle' => 'hdl1',
            'handleExpiresAt' => '2025-01-01T01:00:00Z',
            'mediaType' => 'application/mcp-server-card+json',
            'installability' => 'installable',
            'displayName' => 'GitHub MCP',
            'source' => ['kind' => 'url', 'url' => 'https://example.com/card.json'],
            'provenance' => [
                'authority' => 'https://api.example.com',
                'observedAt' => '2025-01-01T00:00:00Z',
                'mediaType' => 'application/mcp-server-card+json',
            ],
        ]);

        expect($candidate->displayName)->toBe('GitHub MCP')
            ->and($candidate->handle)->toBe('hdl1')
            ->and($candidate->source)->toBeInstanceOf(CatalogCandidateSourceUrl::class);
    });
});

describe('CatalogAiSkillCandidate', function () {
    it('can be created from array', function () {
        $candidate = CatalogAiSkillCandidate::fromArray([
            'kind' => 'ai-skill',
            'handle' => 'hdl2',
            'handleExpiresAt' => '2025-01-01T01:00:00Z',
            'displayName' => 'My AI Skill',
            'source' => ['kind' => 'url', 'url' => 'https://example.com/skill.json'],
            'provenance' => [
                'authority' => 'https://api.example.com',
                'observedAt' => '2025-01-01T00:00:00Z',
            ],
        ]);

        expect($candidate->displayName)->toBe('My AI Skill')
            ->and($candidate->toArray()['installability'])->toBe('not-installable-kind');
    });
});

describe('CatalogSearchSucceeded', function () {
    it('can be created from array', function () {
        $result = CatalogSearchSucceeded::fromArray([
            'searchId' => 'sid1',
            'candidates' => [],
            'truncated' => false,
            'negotiated' => ['runtimeProtocolVersion' => 1, 'grantedCapabilities' => []],
        ]);

        expect($result->kind)->toBe('succeeded')
            ->and($result->searchId)->toBe('sid1')
            ->and($result->truncated)->toBeFalse();
    });

    it('maps mcp-server candidates', function () {
        $result = CatalogSearchSucceeded::fromArray([
            'searchId' => 'sid1',
            'candidates' => [
                [
                    'kind' => 'mcp-server',
                    'handle' => 'h1',
                    'handleExpiresAt' => '2025-01-01T01:00:00Z',
                    'mediaType' => 'application/mcp-server-card+json',
                    'installability' => 'installable',
                    'displayName' => 'Test',
                    'source' => ['kind' => 'url', 'url' => 'https://example.com'],
                    'provenance' => [
                        'authority' => 'https://api.example.com',
                        'observedAt' => '2025-01-01T00:00:00Z',
                        'mediaType' => 'application/mcp-server-card+json',
                    ],
                ],
            ],
            'truncated' => false,
            'negotiated' => ['runtimeProtocolVersion' => 1, 'grantedCapabilities' => []],
        ]);

        expect($result->candidates[0])->toBeInstanceOf(CatalogMcpServerCandidate::class);
    });
});

describe('CatalogUnsupportedKindError', function () {
    it('maps requested and supported kinds', function () {
        $error = CatalogUnsupportedKindError::fromArray([
            'requestedKinds' => ['ai-skill'],
            'supportedKinds' => ['mcp-server'],
            'message' => 'AI skills are not available.',
        ]);

        expect($error->kind)->toBe('unsupported-kind')
            ->and($error->requestedKinds[0])->toBe(\Revolution\Copilot\Enums\CatalogCandidateKind::AiSkill)
            ->and($error->toArray()['supportedKinds'])->toBe(['mcp-server']);
    });
});
