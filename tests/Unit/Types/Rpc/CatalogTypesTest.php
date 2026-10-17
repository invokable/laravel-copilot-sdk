<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogCandidateKind;
use Revolution\Copilot\Enums\CatalogCapability;
use Revolution\Copilot\Enums\CatalogFeedEntryStatus;
use Revolution\Copilot\Enums\CatalogFeedUnavailableReason;
use Revolution\Copilot\Enums\CatalogInvalidRequestField;
use Revolution\Copilot\Enums\CatalogTrustTier;
use Revolution\Copilot\Enums\CatalogUnavailableReason;
use Revolution\Copilot\Types\Rpc\CatalogAiSkillCandidate;
use Revolution\Copilot\Types\Rpc\CatalogCandidateSourceEmbedded;
use Revolution\Copilot\Types\Rpc\CatalogCandidateSourceUrl;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\CatalogFeedEntry;
use Revolution\Copilot\Types\Rpc\CatalogGeneratedFeedDescription;
use Revolution\Copilot\Types\Rpc\CatalogGetGeneratedFeedRequest;
use Revolution\Copilot\Types\Rpc\CatalogGetGeneratedFeedSucceeded;
use Revolution\Copilot\Types\Rpc\CatalogGetSavedFeedRequest;
use Revolution\Copilot\Types\Rpc\CatalogGetSavedFeedSucceeded;
use Revolution\Copilot\Types\Rpc\CatalogMcpServerCandidate;
use Revolution\Copilot\Types\Rpc\CatalogNegotiatedContract;
use Revolution\Copilot\Types\Rpc\CatalogSavedFeedDescription;
use Revolution\Copilot\Types\Rpc\CatalogSearchRequest;
use Revolution\Copilot\Types\Rpc\CatalogSearchSucceeded;
use Revolution\Copilot\Types\Rpc\CatalogTrustSnapshotCurrent;
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

    it('has the trust-snapshot capability', function () {
        expect(CatalogCapability::TrustSnapshot->value)->toBe('trust-snapshot');
    });

    it('exposes feed, category, credential, and session-binding capabilities', function () {
        expect(CatalogCapability::CatalogFeeds->value)->toBe('catalog-feeds')
            ->and(CatalogCapability::CatalogSearchCategories->value)->toBe('catalog-search-categories')
            ->and(CatalogCapability::CatalogSearchCredentialRequired->value)->toBe('catalog-search-credential-required')
            ->and(CatalogCapability::CatalogSearchSessionBound->value)->toBe('catalog-search-session-bound')
            ->and(CatalogInvalidRequestField::Categories->value)->toBe('categories')
            ->and(CatalogInvalidRequestField::Feed->value)->toBe('feed')
            ->and(CatalogInvalidRequestField::TrustTiers->value)->toBe('trustTiers')
            ->and(CatalogUnavailableReason::FeedNotFound->value)->toBe('feed-not-found');
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

    it('round-trips candidate kind enums as wire values', function () {
        $req = CatalogSearchRequest::fromArray([
            'contract' => ['protocolVersion' => 1, 'requiredCapabilities' => []],
            'query' => 'github',
            'kinds' => ['mcp-server'],
        ]);

        expect($req->kinds[0])->toBe(CatalogCandidateKind::McpServer)
            ->and($req->toArray()['kinds'])->toBe(['mcp-server']);
    });

    it('round-trips category filters and omits them when absent', function () {
        $request = CatalogSearchRequest::fromArray([
            'contract' => [
                'protocolVersion' => 3,
                'requiredCapabilities' => [
                    'catalog-search-categories',
                    'catalog-search-credential-required',
                    'catalog-search-session-bound',
                ],
            ],
            'query' => 'design tools',
            'categories' => ['design', 'test'],
            'policySessionId' => 'session-1',
        ]);

        expect($request->categories)->toBe(['design', 'test'])
            ->and($request->policySessionId)->toBe('session-1')
            ->and($request->toArray())->toBe([
                'contract' => [
                    'protocolVersion' => 3,
                    'requiredCapabilities' => [
                        'catalog-search-categories',
                        'catalog-search-credential-required',
                        'catalog-search-session-bound',
                    ],
                ],
                'query' => 'design tools',
                'categories' => ['design', 'test'],
                'policySessionId' => 'session-1',
            ])
            ->and((new CatalogSearchRequest(
                contract: new CatalogClientContract(3, []),
                query: 'tools',
            ))->toArray())->not->toHaveKey('categories')
            ->and((new CatalogSearchRequest(
                contract: new CatalogClientContract(3, []),
                query: 'tools',
            ))->toArray())->not->toHaveKey('policySessionId');
    });
});

describe('Catalog feed requests', function () {
    it('round-trips saved and generated feed requests with optional session binding and trust tiers', function () {
        $contract = new CatalogClientContract(3, [
            CatalogCapability::CatalogFeeds->value,
            CatalogCapability::CatalogSearchCredentialRequired->value,
            CatalogCapability::CatalogSearchSessionBound->value,
        ]);
        $saved = new CatalogGetSavedFeedRequest(
            contract: $contract,
            feedId: 'saved-feed/opaque-id',
            policySessionId: 'session-1',
            trustTiers: [CatalogTrustTier::T1, CatalogTrustTier::T2],
        );
        $generated = new CatalogGetGeneratedFeedRequest(
            contract: $contract,
            strategy: 'telemetry',
            key: 'popular',
            trustTiers: [CatalogTrustTier::T1],
        );

        expect(CatalogGetSavedFeedRequest::fromArray($saved->toArray())->toArray())->toBe($saved->toArray())
            ->and(CatalogGetGeneratedFeedRequest::fromArray($generated->toArray())->toArray())->toBe($generated->toArray())
            ->and($generated->toArray())->not->toHaveKey('policySessionId');
    });
});

describe('Catalog feed result types', function () {
    it('round-trips each entry variant and preserves feed order', function () {
        $available = [
            'status' => 'available',
            'identifier' => 'resource-1',
            'candidate' => [
                'handle' => 'candidate-1',
                'handleExpiresAt' => '2026-10-17T01:00:00Z',
                'kind' => 'ai-skill',
                'mediaType' => 'application/ai-skill',
                'installability' => 'not-installable-kind',
                'displayName' => 'Design Skill',
                'source' => ['kind' => 'url', 'url' => 'https://example.com/skill.json'],
                'provenance' => [
                    'authority' => 'https://api.example.com',
                    'observedAt' => '2026-10-17T00:00:00Z',
                    'mediaType' => 'application/ai-skill',
                ],
            ],
        ];
        $unavailable = [
            'status' => 'unavailable',
            'identifier' => 'resource-2',
            'reason' => 'not-found',
        ];
        $filtered = [
            'status' => 'filtered',
            'identifier' => 'resource-3',
        ];
        $savedData = [
            'kind' => 'succeeded',
            'searchId' => 'search-1',
            'feed' => ['id' => 'feed-1', 'name' => 'Saved', 'version' => 2],
            'entries' => [$available, $unavailable, $filtered],
            'negotiated' => ['runtimeProtocolVersion' => 3, 'grantedCapabilities' => ['catalog-feeds']],
        ];
        $saved = CatalogGetSavedFeedSucceeded::fromArray($savedData);
        $generatedData = [
            'kind' => 'succeeded',
            'searchId' => 'search-2',
            'feed' => ['strategy' => 'telemetry', 'key' => 'popular', 'generatedAt' => '2026-10-17T00:00:00Z'],
            'entries' => [$filtered, $available],
            'negotiated' => ['runtimeProtocolVersion' => 3, 'grantedCapabilities' => ['catalog-feeds']],
        ];
        $generated = CatalogGetGeneratedFeedSucceeded::fromArray($generatedData);

        expect(CatalogFeedEntry::fromArray($available)->status)->toBe(CatalogFeedEntryStatus::Available)
            ->and(CatalogFeedEntry::fromArray($available)->candidate)->toBeInstanceOf(CatalogAiSkillCandidate::class)
            ->and(CatalogFeedEntry::fromArray($unavailable)->reason)->toBe(CatalogFeedUnavailableReason::NotFound)
            ->and(CatalogFeedEntry::fromArray($unavailable)->status)->toBe(CatalogFeedEntryStatus::Unavailable)
            ->and(CatalogFeedEntry::fromArray($filtered)->status)->toBe(CatalogFeedEntryStatus::Filtered)
            ->and($saved->entries[0]->identifier)->toBe('resource-1')
            ->and($saved->entries[1]->identifier)->toBe('resource-2')
            ->and($saved->entries[2]->identifier)->toBe('resource-3')
            ->and($saved->toArray())->toBe($savedData)
            ->and($generated->feed)->toBeInstanceOf(CatalogGeneratedFeedDescription::class)
            ->and($generated->entries[0]->status)->toBe(CatalogFeedEntryStatus::Filtered)
            ->and($generated->toArray())->toBe($generatedData)
            ->and(CatalogSavedFeedDescription::fromArray($savedData['feed'])->toArray())->toBe($savedData['feed']);
    });

    it('omits an absent generated feed timestamp', function () {
        expect((new CatalogGeneratedFeedDescription('telemetry', 'popular'))->toArray())
            ->toBe(['strategy' => 'telemetry', 'key' => 'popular']);
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

    it('can be created with a trust snapshot', function () {
        $candidate = CatalogAiSkillCandidate::fromArray([
            'kind' => 'ai-skill',
            'handle' => 'hdl3',
            'handleExpiresAt' => '2025-01-01T01:00:00Z',
            'displayName' => 'Trusted Skill',
            'source' => ['kind' => 'url', 'url' => 'https://example.com/skill.json'],
            'provenance' => [
                'authority' => 'https://api.example.com',
                'observedAt' => '2025-01-01T00:00:00Z',
            ],
            'trust' => [
                'status' => 'current',
                'tier' => 'T1',
                'eligibility' => 'default',
                'provenance' => ['source' => 'agent-finder', 'observedAt' => '2024-01-01T00:00:00Z'],
            ],
        ]);

        expect($candidate->trust)->toBeInstanceOf(CatalogTrustSnapshotCurrent::class)
            ->and($candidate->toArray()['trust']['status'])->toBe('current');
    });

    it('omits trust when null', function () {
        $candidate = CatalogAiSkillCandidate::fromArray([
            'kind' => 'ai-skill',
            'handle' => 'hdl4',
            'handleExpiresAt' => '2025-01-01T01:00:00Z',
            'displayName' => 'No Trust Skill',
            'source' => ['kind' => 'url', 'url' => 'https://example.com/skill.json'],
            'provenance' => [
                'authority' => 'https://api.example.com',
                'observedAt' => '2025-01-01T00:00:00Z',
            ],
        ]);

        expect($candidate->trust)->toBeNull()
            ->and($candidate->toArray())->not->toHaveKey('trust');
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
            ->and($error->requestedKinds[0])->toBe(CatalogCandidateKind::AiSkill)
            ->and($error->toArray()['supportedKinds'])->toBe(['mcp-server']);
    });
});
