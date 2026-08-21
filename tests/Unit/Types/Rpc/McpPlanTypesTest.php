<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\McpPlanConfigurationOperation;
use Revolution\Copilot\Enums\McpPlanPackageInstallMethod;
use Revolution\Copilot\Enums\McpPlanPackageTransport;
use Revolution\Copilot\Enums\McpPlanPolicyDecision;
use Revolution\Copilot\Enums\McpPlanPolicySource;
use Revolution\Copilot\Enums\McpPlanScope;
use Revolution\Copilot\Types\Rpc\CardDigest;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\CatalogNegotiatedContract;
use Revolution\Copilot\Types\Rpc\McpInstallPlan;
use Revolution\Copilot\Types\Rpc\McpPlanConfigurationChange;
use Revolution\Copilot\Types\Rpc\McpPlanInstallPlanned;
use Revolution\Copilot\Types\Rpc\McpPlanInstallRequest;
use Revolution\Copilot\Types\Rpc\McpPlanInstallSourceCandidate;
use Revolution\Copilot\Types\Rpc\McpPlanInstallSourceCard;
use Revolution\Copilot\Types\Rpc\McpPlanPolicyResult;
use Revolution\Copilot\Types\Rpc\McpPlanProvenance;
use Revolution\Copilot\Types\Rpc\McpPlanRequiredValueEnum;
use Revolution\Copilot\Types\Rpc\McpPlanRequiredValueScalar;
use Revolution\Copilot\Types\Rpc\McpPlanResourceIdentity;
use Revolution\Copilot\Types\Rpc\McpPlanSecretPlaceholder;
use Revolution\Copilot\Types\Rpc\McpPlanTarget;
use Revolution\Copilot\Types\Rpc\McpPlanTransportChoicePackage;
use Revolution\Copilot\Types\Rpc\McpPlanTransportChoiceRemote;
use Revolution\Copilot\Types\Rpc\McpServerCardEmbedded;
use Revolution\Copilot\Types\Rpc\McpServerCardUrl;

describe('CardDigest', function () {
    it('can be created from array', function () {
        $digest = CardDigest::fromArray([
            'algorithm' => 'sha256-rfc8785',
            'value' => 'abc123',
        ]);

        expect($digest->value)->toBe('abc123')
            ->and($digest->toArray()['algorithm'])->toBe('sha256-rfc8785');
    });
});

describe('McpPlanResourceIdentity', function () {
    it('can be created from array with all fields', function () {
        $id = McpPlanResourceIdentity::fromArray([
            'canonicalName' => 'github.com/owner/repo',
            'serverName' => 'my-server',
            'version' => '1.0.0',
            'registryId' => 'ghcr.io/owner/repo',
        ]);

        expect($id->canonicalName)->toBe('github.com/owner/repo')
            ->and($id->version)->toBe('1.0.0');
    });

    it('omits null fields in toArray', function () {
        $id = new McpPlanResourceIdentity('cn', 'sn');
        $arr = $id->toArray();

        expect($arr)->not->toHaveKey('version')
            ->and($arr)->not->toHaveKey('registryId');
    });
});

describe('McpPlanSecretPlaceholder', function () {
    it('can be created from array', function () {
        $sp = McpPlanSecretPlaceholder::fromArray([
            'key' => 'API_KEY',
            'placeholder' => '$API_KEY',
            'title' => 'API Key',
        ]);

        expect($sp->key)->toBe('API_KEY')
            ->and($sp->placeholder)->toBe('$API_KEY')
            ->and($sp->title)->toBe('API Key');
    });
});

describe('McpPlanRequiredValueScalar', function () {
    it('can be created from array', function () {
        $v = McpPlanRequiredValueScalar::fromArray([
            'kind' => 'scalar',
            'key' => 'PORT',
            'category' => 'environment-variable',
            'valueType' => 'number',
            'required' => true,
            'isRepeated' => false,
        ]);

        expect($v->key)->toBe('PORT')
            ->and($v->required)->toBeTrue()
            ->and($v->isRepeated)->toBeFalse();
    });
});

describe('McpPlanRequiredValueEnum', function () {
    it('can be created from array', function () {
        $v = McpPlanRequiredValueEnum::fromArray([
            'kind' => 'enum',
            'key' => 'LEVEL',
            'category' => 'environment-variable',
            'valueType' => 'enum',
            'required' => true,
            'isRepeated' => false,
            'enumValues' => ['debug', 'info', 'warn'],
        ]);

        expect($v->enumValues)->toBe(['debug', 'info', 'warn']);
    });
});

describe('McpPlanTarget', function () {
    it('can be created from array', function () {
        $target = McpPlanTarget::fromArray(['scope' => 'user', 'configKey' => 'my-server']);

        expect($target->scope)->toBe(McpPlanScope::User)
            ->and($target->configKey)->toBe('my-server');
    });
});

describe('McpPlanPolicyResult', function () {
    it('can be created from array', function () {
        $policy = McpPlanPolicyResult::fromArray([
            'decision' => 'allowed',
            'source' => 'registry-policy',
        ]);

        expect($policy->decision)->toBe(McpPlanPolicyDecision::Allowed)
            ->and($policy->source)->toBe(McpPlanPolicySource::RegistryPolicy)
            ->and($policy->reason)->toBeNull();
    });

    it('includes reason when present', function () {
        $policy = McpPlanPolicyResult::fromArray([
            'decision' => 'blocked',
            'source' => 'enterprise-allowlist',
            'reason' => 'Not on allowlist.',
        ]);

        expect($policy->reason)->toBe('Not on allowlist.');
    });
});

describe('McpPlanConfigurationChange', function () {
    it('can be created from array', function () {
        $change = McpPlanConfigurationChange::fromArray([
            'operation' => 'add',
            'scope' => 'user',
            'configKey' => 'my-server',
            'changedFields' => ['command', 'args'],
            'secretReferences' => [],
        ]);

        expect($change->operation)->toBe(McpPlanConfigurationOperation::Add)
            ->and($change->changedFields)->toBe(['command', 'args']);
    });
});

describe('McpPlanTransportChoicePackage', function () {
    it('can be created from array', function () {
        $choice = McpPlanTransportChoicePackage::fromArray([
            'choiceId' => 'c1',
            'transport' => 'stdio',
            'installMethod' => 'package',
            'packageType' => 'npm',
            'packageIdentifier' => '@my-org/my-mcp-server',
            'requiredValues' => [],
            'secretPlaceholders' => [],
        ]);

        expect($choice->transport)->toBe(McpPlanPackageTransport::Stdio)
            ->and($choice->installMethod)->toBe(McpPlanPackageInstallMethod::Package)
            ->and($choice->packageIdentifier)->toBe('@my-org/my-mcp-server');
    });
});

describe('McpServerCardUrl', function () {
    it('can be created from array', function () {
        $card = McpServerCardUrl::fromArray([
            'kind' => 'url',
            'mediaType' => 'application/mcp-server-card+json',
            'url' => 'https://example.com/card.json',
        ]);

        expect($card->url)->toBe('https://example.com/card.json')
            ->and($card->toArray()['kind'])->toBe('url');
    });
});

describe('McpServerCardEmbedded', function () {
    it('can be created from array', function () {
        $card = McpServerCardEmbedded::fromArray([
            'kind' => 'embedded',
            'mediaType' => 'application/mcp-server-card+json',
            'data' => '{"name":"test"}',
        ]);

        expect($card->data)->toBe('{"name":"test"}')
            ->and($card->toArray()['kind'])->toBe('embedded');
    });
});

describe('McpPlanInstallSourceCandidate', function () {
    it('can be created from array', function () {
        $src = McpPlanInstallSourceCandidate::fromArray([
            'kind' => 'candidate',
            'candidateHandle' => 'hdl1',
            'searchId' => 'sid1',
        ]);

        expect($src->candidateHandle)->toBe('hdl1')
            ->and($src->searchId)->toBe('sid1');
    });
});

describe('McpPlanInstallSourceCard', function () {
    it('can be created from array with url card', function () {
        $src = McpPlanInstallSourceCard::fromArray([
            'kind' => 'card',
            'card' => [
                'kind' => 'url',
                'mediaType' => 'application/mcp-server-card+json',
                'url' => 'https://example.com/card.json',
            ],
        ]);

        expect($src->card)->toBeInstanceOf(McpServerCardUrl::class);
    });
});

describe('McpPlanInstallRequest', function () {
    it('can be created from array with candidate source', function () {
        $req = McpPlanInstallRequest::fromArray([
            'contract' => ['protocolVersion' => 1, 'requiredCapabilities' => []],
            'source' => ['kind' => 'candidate', 'candidateHandle' => 'h1', 'searchId' => 's1'],
        ]);

        expect($req->source)->toBeInstanceOf(McpPlanInstallSourceCandidate::class)
            ->and($req->scope)->toBeNull();
    });

    it('includes scope when present', function () {
        $req = McpPlanInstallRequest::fromArray([
            'contract' => ['protocolVersion' => 1, 'requiredCapabilities' => []],
            'source' => ['kind' => 'candidate', 'candidateHandle' => 'h1', 'searchId' => 's1'],
            'scope' => 'user',
        ]);

        expect($req->scope)->toBe(McpPlanScope::User);
    });

    it('omits scope from toArray when null', function () {
        $req = new McpPlanInstallRequest(
            contract: new CatalogClientContract(1, []),
            source: new McpPlanInstallSourceCandidate(
                \Revolution\Copilot\Enums\McpPlanInstallSourceCandidateKind::Candidate,
                'h1',
                's1',
            ),
        );

        expect($req->toArray())->not->toHaveKey('scope');
    });
});

describe('McpPlanInstallPlanned', function () {
    it('has kind = planned', function () {
        $planned = McpPlanInstallPlanned::fromArray([
            'plan' => [
                'planHandle' => 'ph1',
                'planHandleExpiresAt' => '2025-12-31T00:00:00Z',
                'identity' => ['canonicalName' => 'cn', 'serverName' => 'sn'],
                'provenance' => [
                    'authority' => 'auth',
                    'validatedAt' => '2025-01-01T00:00:00Z',
                    'cardDigest' => ['algorithm' => 'sha256-rfc8785', 'value' => 'abc'],
                    'mediaType' => 'application/mcp-server-card+json',
                ],
                'transportChoices' => [
                    [
                        'choiceId' => 'c1',
                        'transport' => 'stdio',
                        'installMethod' => 'package',
                        'packageType' => 'npm',
                        'packageIdentifier' => '@org/pkg',
                        'requiredValues' => [],
                        'secretPlaceholders' => [],
                    ],
                ],
                'target' => ['scope' => 'user', 'configKey' => 'key1'],
                'policy' => ['decision' => 'allowed', 'source' => 'none'],
                'configurationChanges' => [],
                'reloadRequired' => false,
                'requiresInteractiveConfiguration' => false,
            ],
            'negotiated' => ['runtimeProtocolVersion' => 1, 'grantedCapabilities' => []],
        ]);

        expect($planned->kind)->toBe('planned')
            ->and($planned->plan)->toBeInstanceOf(McpInstallPlan::class)
            ->and($planned->plan->transportChoices[0])->toBeInstanceOf(McpPlanTransportChoicePackage::class);
    });
});

describe('McpPlanTransportChoiceRemote', function () {
    it('can be created from array', function () {
        $choice = McpPlanTransportChoiceRemote::fromArray([
            'choiceId' => 'rc1',
            'transport' => 'streamable-http',
            'installMethod' => 'remote',
            'endpoint' => 'https://mcp.example.com/sse',
            'requiredValues' => [],
            'secretPlaceholders' => [],
        ]);

        expect($choice->choiceId)->toBe('rc1')
            ->and($choice->transport)->toBe(\Revolution\Copilot\Enums\McpPlanRemoteTransport::StreamableHttp)
            ->and($choice->installMethod)->toBe(\Revolution\Copilot\Enums\McpPlanRemoteInstallMethod::Remote)
            ->and($choice->endpoint)->toBe('https://mcp.example.com/sse');
    });

    it('round-trips via toArray', function () {
        $choice = McpPlanTransportChoiceRemote::fromArray([
            'choiceId' => 'rc2',
            'transport' => 'sse',
            'installMethod' => 'remote',
            'endpoint' => 'https://mcp.example.com/events',
            'requiredValues' => [
                ['kind' => 'scalar', 'key' => 'TOKEN', 'category' => 'environment-variable', 'valueType' => 'string', 'required' => true, 'isRepeated' => false],
            ],
            'secretPlaceholders' => [
                ['key' => 'API_SECRET', 'placeholder' => '$API_SECRET', 'title' => 'API Secret'],
            ],
        ]);

        $arr = $choice->toArray();

        expect($arr['choiceId'])->toBe('rc2')
            ->and($arr['transport'])->toBe('sse')
            ->and($arr['installMethod'])->toBe('remote')
            ->and($arr['requiredValues'])->toHaveCount(1)
            ->and($arr['secretPlaceholders'])->toHaveCount(1);
    });
});

describe('McpPlanInstallPlanned with remote transport', function () {
    it('builds a plan with a remote transport choice', function () {
        $planned = McpPlanInstallPlanned::fromArray([
            'plan' => [
                'planHandle' => 'ph-remote',
                'planHandleExpiresAt' => '2025-12-31T00:00:00Z',
                'identity' => ['canonicalName' => 'io.example/remote-mcp', 'serverName' => 'remote-mcp'],
                'provenance' => [
                    'authority' => 'https://registry.example.com',
                    'validatedAt' => '2025-01-01T00:00:00Z',
                    'cardDigest' => ['algorithm' => 'sha256-rfc8785', 'value' => 'deadbeef'],
                    'mediaType' => 'application/mcp-server-card+json',
                ],
                'transportChoices' => [
                    [
                        'choiceId' => 'rc1',
                        'transport' => 'streamable-http',
                        'installMethod' => 'remote',
                        'endpoint' => 'https://mcp.example.com/mcp',
                        'requiredValues' => [],
                        'secretPlaceholders' => [],
                    ],
                ],
                'target' => ['scope' => 'user', 'configKey' => 'remote-mcp'],
                'policy' => ['decision' => 'allowed', 'source' => 'none'],
                'configurationChanges' => [],
                'reloadRequired' => true,
                'requiresInteractiveConfiguration' => false,
            ],
            'negotiated' => ['runtimeProtocolVersion' => 1, 'grantedCapabilities' => ['mcp-server-card']],
        ]);

        expect($planned->kind)->toBe('planned')
            ->and($planned->plan->reloadRequired)->toBeTrue()
            ->and($planned->plan->transportChoices[0])->toBeInstanceOf(McpPlanTransportChoiceRemote::class)
            ->and($planned->plan->transportChoices[0]->endpoint)->toBe('https://mcp.example.com/mcp');
    });
});

describe('McpPlanInstallPlanned toArray round-trip', function () {
    it('serializes and deserializes symmetrically', function () {
        $original = [
            'plan' => [
                'planHandle' => 'ph-rt',
                'planHandleExpiresAt' => '2025-12-31T00:00:00Z',
                'identity' => ['canonicalName' => 'io.example/rt-mcp', 'serverName' => 'rt-mcp'],
                'provenance' => [
                    'authority' => 'https://registry.example.com',
                    'validatedAt' => '2025-01-01T00:00:00Z',
                    'cardDigest' => ['algorithm' => 'sha256-rfc8785', 'value' => 'aabbcc'],
                    'mediaType' => 'application/mcp-server-card+json',
                ],
                'transportChoices' => [
                    [
                        'choiceId' => 'c1',
                        'transport' => 'stdio',
                        'installMethod' => 'package',
                        'packageType' => 'npm',
                        'packageIdentifier' => '@org/rt-pkg',
                        'requiredValues' => [],
                        'secretPlaceholders' => [],
                    ],
                ],
                'target' => ['scope' => 'user', 'configKey' => 'rt-mcp'],
                'policy' => ['decision' => 'allowed', 'source' => 'none'],
                'configurationChanges' => [
                    ['operation' => 'add', 'scope' => 'user', 'configKey' => 'rt-mcp', 'changedFields' => ['command'], 'secretReferences' => []],
                ],
                'reloadRequired' => false,
                'requiresInteractiveConfiguration' => false,
            ],
            'negotiated' => ['runtimeProtocolVersion' => 2, 'grantedCapabilities' => []],
        ];

        $planned = McpPlanInstallPlanned::fromArray($original);
        $arr = $planned->toArray();

        expect($arr['kind'])->toBe('planned')
            ->and($arr['plan']['planHandle'])->toBe('ph-rt')
            ->and($arr['plan']['configurationChanges'])->toHaveCount(1)
            ->and($arr['plan']['configurationChanges'][0]['operation'])->toBe('add')
            ->and($arr['negotiated']['runtimeProtocolVersion'])->toBe(2);
    });
});

describe('McpPlanInstallSourceCard with embedded card', function () {
    it('can be created from array with embedded card', function () {
        $src = McpPlanInstallSourceCard::fromArray([
            'kind' => 'card',
            'card' => [
                'kind' => 'embedded',
                'mediaType' => 'application/mcp-server-card+json',
                'data' => '{"name":"embedded-mcp"}',
            ],
        ]);

        expect($src->card)->toBeInstanceOf(McpServerCardEmbedded::class)
            ->and($src->card->data)->toBe('{"name":"embedded-mcp"}')
            ->and($src->toArray()['card']['kind'])->toBe('embedded');
    });
});

describe('CatalogNegotiationRefusedError', function () {
    it('can be created from array and round-trips via toArray', function () {
        $error = \Revolution\Copilot\Types\Rpc\CatalogNegotiationRefusedError::fromArray([
            'reason' => 'unsupported-protocol-version',
            'runtimeProtocolVersion' => 5,
            'minimumSupportedProtocolVersion' => 3,
            'supportedCapabilities' => ['mcp-server-card', 'mcp-install-planning'],
            'unsupportedCapabilities' => ['future-feature'],
            'message' => 'Caller protocol version 1 is below minimum 3.',
        ]);

        expect($error->kind)->toBe('negotiation-refused')
            ->and($error->runtimeProtocolVersion)->toBe(5)
            ->and($error->minimumSupportedProtocolVersion)->toBe(3)
            ->and($error->supportedCapabilities)->toHaveCount(2)
            ->and($error->unsupportedCapabilities)->toBe(['future-feature'])
            ->and($error->message)->toContain('protocol version');

        $arr = $error->toArray();

        expect($arr['kind'])->toBe('negotiation-refused')
            ->and($arr['supportedCapabilities'])->toBe(['mcp-server-card', 'mcp-install-planning'])
            ->and($arr['unsupportedCapabilities'])->toBe(['future-feature']);
    });
});
