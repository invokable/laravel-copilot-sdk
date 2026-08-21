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
            'mediaType' => 'application/vnd.github.mcp-server-card.v0+json',
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
            'mediaType' => 'application/vnd.github.mcp-server-card.v0+json',
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
                'mediaType' => 'application/vnd.github.mcp-server-card.v0+json',
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
                    'mediaType' => 'application/vnd.github.mcp-server-card.v0+json',
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
