<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AgentDiscoveryPathScope;
use Revolution\Copilot\Enums\InstructionDiscoveryPathKind;
use Revolution\Copilot\Enums\InstructionSourceLocation;
use Revolution\Copilot\Enums\SkillDiscoveryScope;
use Revolution\Copilot\Enums\SubagentSettingsEntryContextTier;
use Revolution\Copilot\Types\MemoryConfiguration;
use Revolution\Copilot\Types\NamedProviderConfig;
use Revolution\Copilot\Types\ProviderModelConfig;
use Revolution\Copilot\Types\Rpc\AgentDiscoveryPath;
use Revolution\Copilot\Types\Rpc\AgentDiscoveryPathList;
use Revolution\Copilot\Types\Rpc\AgentsGetDiscoveryPathsRequest;
use Revolution\Copilot\Types\Rpc\InstructionDiscoveryPath;
use Revolution\Copilot\Types\Rpc\InstructionDiscoveryPathList;
use Revolution\Copilot\Types\Rpc\InstructionsGetDiscoveryPathsRequest;
use Revolution\Copilot\Types\Rpc\ModelBillingTokenPrices;
use Revolution\Copilot\Types\Rpc\ModelBillingTokenPricesLongContext;
use Revolution\Copilot\Types\Rpc\PlanReadSqlTodosWithDependenciesResult;
use Revolution\Copilot\Types\Rpc\PlanSqlTodoDependency;
use Revolution\Copilot\Types\Rpc\ProviderEndpoint;
use Revolution\Copilot\Types\Rpc\ProviderGetEndpointRequest;
use Revolution\Copilot\Types\Rpc\ProviderSessionToken;
use Revolution\Copilot\Types\Rpc\SkillDiscoveryPath;
use Revolution\Copilot\Types\Rpc\SkillDiscoveryPathList;
use Revolution\Copilot\Types\Rpc\SkillsGetDiscoveryPathsRequest;
use Revolution\Copilot\Types\Rpc\SubagentSettings;
use Revolution\Copilot\Types\Rpc\SubagentSettingsEntry;
use Revolution\Copilot\Types\Rpc\ToolsUpdateSubagentSettingsResult;
use Revolution\Copilot\Types\Rpc\UpdateSubagentSettingsRequest;

describe('MemoryConfiguration', function () {
    it('can be created with all fields', function () {
        $mem = MemoryConfiguration::fromArray([
            'enabled' => true,
        ]);

        expect($mem->enabled)->toBeTrue();
    });

    it('handles missing fields with defaults', function () {
        $mem = MemoryConfiguration::fromArray([]);

        expect($mem->enabled)->toBeFalse();
    });

    it('converts to array', function () {
        $mem = new MemoryConfiguration(enabled: true);

        $arr = $mem->toArray();

        expect($arr)->toHaveKey('enabled', true);
    });

    it('implements Arrayable interface', function () {
        expect(new MemoryConfiguration(enabled: false))->toBeInstanceOf(Arrayable::class);
    });
});

describe('NamedProviderConfig', function () {
    it('can be created with all fields', function () {
        $provider = NamedProviderConfig::fromArray([
            'name' => 'openai-custom',
            'type' => 'openai',
            'apiKey' => 'sk-test',
            'baseUrl' => 'https://api.openai.com/v1',
        ]);

        expect($provider->name)->toBe('openai-custom')
            ->and($provider->type)->toBe('openai')
            ->and($provider->apiKey)->toBe('sk-test')
            ->and($provider->baseUrl)->toBe('https://api.openai.com/v1');
    });

    it('handles default values', function () {
        $provider = NamedProviderConfig::fromArray(['name' => 'my-provider', 'baseUrl' => 'https://api.openai.com/v1', 'type' => 'openai']);

        expect($provider->apiKey)->toBeNull()
            ->and($provider->bearerToken)->toBeNull();
    });

    it('converts to array', function () {
        $provider = new NamedProviderConfig(name: 'test', baseUrl: 'https://api.example.com');

        expect($provider->toArray())->toHaveKey('name', 'test')
            ->and($provider->toArray())->toHaveKey('baseUrl', 'https://api.example.com');
    });

    it('implements Arrayable interface', function () {
        expect(new NamedProviderConfig(name: 'x', baseUrl: 'https://api.example.com'))->toBeInstanceOf(Arrayable::class);
    });
});

describe('ProviderModelConfig', function () {
    it('can be created with all fields', function () {
        $model = ProviderModelConfig::fromArray([
            'id' => 'gpt-5',
            'provider' => 'openai-custom',
            'modelId' => 'gpt-5-latest',
        ]);

        expect($model->id)->toBe('gpt-5')
            ->and($model->provider)->toBe('openai-custom')
            ->and($model->modelId)->toBe('gpt-5-latest');
    });

    it('handles optional fields', function () {
        $model = ProviderModelConfig::fromArray(['id' => 'm1', 'provider' => 'p1']);

        expect($model->modelId)->toBeNull();
    });

    it('converts to array', function () {
        $model = new ProviderModelConfig(id: 'm1', provider: 'p1', modelId: 'gpt-5');

        expect($model->toArray())->toHaveKey('id', 'm1')
            ->and($model->toArray())->toHaveKey('provider', 'p1')
            ->and($model->toArray())->toHaveKey('modelId', 'gpt-5');
    });
});

describe('ModelBillingTokenPricesLongContext', function () {
    it('can be created from array', function () {
        $lc = ModelBillingTokenPricesLongContext::fromArray([
            'inputPrice' => 5.0,
            'outputPrice' => 15.0,
        ]);

        expect($lc->inputPrice)->toBe(5.0)
            ->and($lc->outputPrice)->toBe(15.0);
    });

    it('handles null values', function () {
        $lc = ModelBillingTokenPricesLongContext::fromArray([]);

        expect($lc->inputPrice)->toBeNull()
            ->and($lc->outputPrice)->toBeNull();
    });

    it('converts to array', function () {
        $lc = new ModelBillingTokenPricesLongContext(inputPrice: 3.0, outputPrice: 10.0);

        expect($lc->toArray())->toHaveKey('inputPrice', 3.0)
            ->and($lc->toArray())->toHaveKey('outputPrice', 10.0);
    });
});

describe('ModelBillingTokenPrices', function () {
    it('can be created from array with all fields', function () {
        $prices = ModelBillingTokenPrices::fromArray([
            'inputPrice' => 2.5,
            'outputPrice' => 7.5,
            'longContext' => [
                'inputPrice' => 5.0,
                'outputPrice' => 15.0,
            ],
        ]);

        expect($prices->inputPrice)->toBe(2.5)
            ->and($prices->outputPrice)->toBe(7.5)
            ->and($prices->longContext)->toBeInstanceOf(ModelBillingTokenPricesLongContext::class)
            ->and($prices->longContext->inputPrice)->toBe(5.0);
    });

    it('handles missing fields', function () {
        $prices = ModelBillingTokenPrices::fromArray([]);

        expect($prices->inputPrice)->toBeNull()
            ->and($prices->longContext)->toBeNull();
    });

    it('converts to array', function () {
        $prices = new ModelBillingTokenPrices(inputPrice: 1.0, outputPrice: 3.0);

        expect($prices->toArray())->toHaveKey('inputPrice', 1.0)
            ->and($prices->toArray())->not->toHaveKey('longContext');
    });

    it('implements Arrayable interface', function () {
        expect(new ModelBillingTokenPrices)->toBeInstanceOf(Arrayable::class);
    });
});

describe('AgentDiscoveryPath', function () {
    it('can be created from array with all fields', function () {
        $path = AgentDiscoveryPath::fromArray([
            'path' => '/home/user/.copilot/agents',
            'scope' => 'user',
            'preferredForCreation' => true,
        ]);

        expect($path->path)->toBe('/home/user/.copilot/agents')
            ->and($path->scope)->toBe(AgentDiscoveryPathScope::User)
            ->and($path->preferredForCreation)->toBeTrue();
    });

    it('handles default values', function () {
        $path = AgentDiscoveryPath::fromArray(['path' => '/some/path', 'scope' => 'project']);

        expect($path->preferredForCreation)->toBeFalse();
    });

    it('converts to array including false preferredForCreation', function () {
        $path = new AgentDiscoveryPath(path: '/some/path', scope: AgentDiscoveryPathScope::Project, preferredForCreation: false);

        $arr = $path->toArray();
        expect($arr)->toHaveKey('path', '/some/path')
            ->and($arr)->toHaveKey('scope', 'project')
            ->and($arr)->toHaveKey('preferredForCreation', false);
    });

    it('implements Arrayable interface', function () {
        expect(new AgentDiscoveryPath(path: '/p', scope: AgentDiscoveryPathScope::User, preferredForCreation: false))->toBeInstanceOf(Arrayable::class);
    });
});

describe('AgentDiscoveryPathList', function () {
    it('can be created from array with paths', function () {
        $list = AgentDiscoveryPathList::fromArray([
            'paths' => [
                ['path' => '/home/user/.copilot/agents', 'scope' => 'user', 'preferredForCreation' => true],
                ['path' => '/workspace/.copilot/agents', 'scope' => 'project', 'preferredForCreation' => false],
            ],
        ]);

        expect($list->paths)->toHaveCount(2)
            ->and($list->paths[0])->toBeInstanceOf(AgentDiscoveryPath::class)
            ->and($list->paths[0]->scope)->toBe(AgentDiscoveryPathScope::User);
    });

    it('handles empty paths', function () {
        $list = AgentDiscoveryPathList::fromArray([]);

        expect($list->paths)->toBeEmpty();
    });

    it('converts to array', function () {
        $list = AgentDiscoveryPathList::fromArray([
            'paths' => [
                ['path' => '/p', 'scope' => 'user'],
            ],
        ]);

        expect($list->toArray())->toHaveKey('paths');
    });
});

describe('AgentsGetDiscoveryPathsRequest', function () {
    it('can be created from empty array', function () {
        $req = AgentsGetDiscoveryPathsRequest::fromArray([]);

        expect($req->excludeHostAgents)->toBeNull();
    });

    it('can be created with excludeHostAgents', function () {
        $req = AgentsGetDiscoveryPathsRequest::fromArray(['excludeHostAgents' => true]);

        expect($req->excludeHostAgents)->toBeTrue();
    });

    it('converts to array without null values', function () {
        $req = new AgentsGetDiscoveryPathsRequest;

        expect($req->toArray())->toBeEmpty();
    });
});

describe('SkillDiscoveryPath', function () {
    it('can be created from array with all fields', function () {
        $path = SkillDiscoveryPath::fromArray([
            'path' => '/home/user/.copilot/skills',
            'scope' => 'project',
            'preferredForCreation' => true,
        ]);

        expect($path->path)->toBe('/home/user/.copilot/skills')
            ->and($path->scope)->toBe(SkillDiscoveryScope::Project)
            ->and($path->preferredForCreation)->toBeTrue();
    });

    it('converts to array including false preferredForCreation', function () {
        $path = new SkillDiscoveryPath(path: '/p', scope: SkillDiscoveryScope::Project, preferredForCreation: false);

        expect($path->toArray())->toHaveKey('preferredForCreation', false);
    });
});

describe('SkillDiscoveryPathList', function () {
    it('can be created from array with paths', function () {
        $list = SkillDiscoveryPathList::fromArray([
            'paths' => [
                ['path' => '/home/user/.copilot/skills', 'scope' => 'project', 'preferredForCreation' => true],
            ],
        ]);

        expect($list->paths)->toHaveCount(1)
            ->and($list->paths[0])->toBeInstanceOf(SkillDiscoveryPath::class);
    });
});

describe('SkillsGetDiscoveryPathsRequest', function () {
    it('can be created with excludeHostSkills', function () {
        $req = SkillsGetDiscoveryPathsRequest::fromArray(['excludeHostSkills' => true]);

        expect($req->excludeHostSkills)->toBeTrue();
    });

    it('converts to array without null', function () {
        $req = new SkillsGetDiscoveryPathsRequest;

        expect($req->toArray())->toBeEmpty();
    });
});

describe('InstructionDiscoveryPath', function () {
    it('can be created from array with all fields', function () {
        $path = InstructionDiscoveryPath::fromArray([
            'path' => '/home/user/.copilot/instructions.md',
            'location' => 'user',
            'kind' => 'file',
            'preferredForCreation' => true,
        ]);

        expect($path->path)->toBe('/home/user/.copilot/instructions.md')
            ->and($path->location)->toBe(InstructionSourceLocation::USER)
            ->and($path->kind)->toBe(InstructionDiscoveryPathKind::File)
            ->and($path->preferredForCreation)->toBeTrue();
    });

    it('converts to array with false preferredForCreation', function () {
        $path = new InstructionDiscoveryPath(path: '/p', location: InstructionSourceLocation::REPOSITORY, kind: InstructionDiscoveryPathKind::File, preferredForCreation: false);

        expect($path->toArray())->toHaveKey('preferredForCreation', false);
    });
});

describe('InstructionDiscoveryPathList', function () {
    it('can be created from array', function () {
        $list = InstructionDiscoveryPathList::fromArray([
            'paths' => [
                ['path' => '/p', 'location' => 'user', 'kind' => 'file'],
            ],
        ]);

        expect($list->paths)->toHaveCount(1)
            ->and($list->paths[0])->toBeInstanceOf(InstructionDiscoveryPath::class);
    });

    it('handles empty paths', function () {
        $list = InstructionDiscoveryPathList::fromArray([]);

        expect($list->paths)->toBeEmpty();
    });
});

describe('InstructionsGetDiscoveryPathsRequest', function () {
    it('can be created with excludeHostInstructions', function () {
        $req = InstructionsGetDiscoveryPathsRequest::fromArray(['excludeHostInstructions' => true]);

        expect($req->excludeHostInstructions)->toBeTrue();
    });

    it('converts to empty array when null', function () {
        $req = new InstructionsGetDiscoveryPathsRequest;

        expect($req->toArray())->toBeEmpty();
    });
});

describe('PlanSqlTodoDependency', function () {
    it('can be created from array', function () {
        $dep = PlanSqlTodoDependency::fromArray([
            'todoId' => 'task-1',
            'dependsOn' => 'task-2',
        ]);

        expect($dep->todoId)->toBe('task-1')
            ->and($dep->dependsOn)->toBe('task-2');
    });

    it('converts to array', function () {
        $dep = new PlanSqlTodoDependency(todoId: 'a', dependsOn: 'b');

        expect($dep->toArray())->toBe(['dependsOn' => 'b', 'todoId' => 'a']);
    });
});

describe('PlanReadSqlTodosWithDependenciesResult', function () {
    it('can be created from array with rows and dependencies', function () {
        $result = PlanReadSqlTodosWithDependenciesResult::fromArray([
            'rows' => [
                ['id' => 'task-1', 'title' => 'Task 1', 'status' => 'pending'],
            ],
            'dependencies' => [
                ['todoId' => 'task-1', 'dependsOn' => 'task-2'],
            ],
        ]);

        expect($result->rows)->toHaveCount(1)
            ->and($result->dependencies)->toHaveCount(1)
            ->and($result->dependencies[0])->toBeInstanceOf(PlanSqlTodoDependency::class)
            ->and($result->dependencies[0]->todoId)->toBe('task-1');
    });

    it('handles empty result', function () {
        $result = PlanReadSqlTodosWithDependenciesResult::fromArray([]);

        expect($result->rows)->toBeEmpty()
            ->and($result->dependencies)->toBeEmpty();
    });

    it('converts to array', function () {
        $result = PlanReadSqlTodosWithDependenciesResult::fromArray([
            'rows' => [['id' => 'task-1']],
            'dependencies' => [],
        ]);

        expect($result->toArray())->toHaveKey('rows')
            ->and($result->toArray())->toHaveKey('dependencies');
    });
});

describe('ProviderSessionToken', function () {
    it('can be created from array', function () {
        $token = ProviderSessionToken::fromArray([
            'token' => 'sk-test-token',
            'expiresAt' => '2025-12-31T00:00:00Z',
        ]);

        expect($token->token)->toBe('sk-test-token')
            ->and($token->expiresAt)->toBe('2025-12-31T00:00:00Z');
    });

    it('handles optional expiresAt', function () {
        $token = ProviderSessionToken::fromArray(['token' => 'test']);

        expect($token->expiresAt)->toBeNull();
    });

    it('converts to array', function () {
        $token = new ProviderSessionToken(header: 'X-Session-Token', token: 'test-token');

        expect($token->toArray())->toHaveKey('token', 'test-token');
    });
});

describe('ProviderEndpoint', function () {
    it('can be created from array with all fields', function () {
        $endpoint = ProviderEndpoint::fromArray([
            'baseUrl' => 'https://api.openai.com/v1',
            'type' => 'openai',
            'sessionToken' => ['token' => 'sk-session'],
            'headers' => ['Authorization' => 'Bearer sk-session'],
            'apiKey' => 'sk-key',
        ]);

        expect($endpoint->baseUrl)->toBe('https://api.openai.com/v1')
            ->and($endpoint->type)->toBe('openai')
            ->and($endpoint->sessionToken)->toBeInstanceOf(ProviderSessionToken::class)
            ->and($endpoint->headers)->toBe(['Authorization' => 'Bearer sk-session'])
            ->and($endpoint->apiKey)->toBe('sk-key');
    });

    it('handles minimal data', function () {
        $endpoint = ProviderEndpoint::fromArray([
            'baseUrl' => 'https://api.example.com',
            'type' => 'custom',
        ]);

        expect($endpoint->sessionToken)->toBeNull()
            ->and($endpoint->headers)->toBeEmpty()
            ->and($endpoint->apiKey)->toBeNull();
    });

    it('converts to array', function () {
        $endpoint = new ProviderEndpoint(baseUrl: 'https://api.example.com', type: 'openai', headers: []);

        expect($endpoint->toArray())->toHaveKey('baseUrl')
            ->and($endpoint->toArray())->toHaveKey('type')
            ->and($endpoint->toArray())->toHaveKey('headers');
    });

    it('implements Arrayable interface', function () {
        expect(new ProviderEndpoint(baseUrl: 'https://api.example.com', type: 'openai', headers: []))->toBeInstanceOf(Arrayable::class);
    });
});

describe('ProviderGetEndpointRequest', function () {
    it('can be created with modelId', function () {
        $req = ProviderGetEndpointRequest::fromArray(['modelId' => 'gpt-5']);

        expect($req->modelId)->toBe('gpt-5');
    });

    it('handles no modelId', function () {
        $req = ProviderGetEndpointRequest::fromArray([]);

        expect($req->modelId)->toBeNull();
    });

    it('converts to empty array when null', function () {
        $req = new ProviderGetEndpointRequest;

        expect($req->toArray())->toBeEmpty();
    });
});

describe('SubagentSettingsEntry', function () {
    it('can be created from array with all fields', function () {
        $entry = SubagentSettingsEntry::fromArray([
            'contextTier' => 'inherit',
            'effortLevel' => 'medium',
            'model' => 'gpt-5',
        ]);

        expect($entry->contextTier)->toBe(SubagentSettingsEntryContextTier::Inherit)
            ->and($entry->effortLevel)->toBe('medium')
            ->and($entry->model)->toBe('gpt-5');
    });

    it('handles optional fields', function () {
        $entry = SubagentSettingsEntry::fromArray([]);

        expect($entry->contextTier)->toBeNull()
            ->and($entry->effortLevel)->toBeNull()
            ->and($entry->model)->toBeNull();
    });

    it('converts to array', function () {
        $entry = new SubagentSettingsEntry(contextTier: SubagentSettingsEntryContextTier::Default, effortLevel: 'high', model: 'gpt-5');

        expect($entry->toArray())->toHaveKey('contextTier', 'default')
            ->and($entry->toArray())->toHaveKey('effortLevel', 'high')
            ->and($entry->toArray())->toHaveKey('model', 'gpt-5');
    });
});

describe('SubagentSettings', function () {
    it('can be created from array with agents', function () {
        $settings = SubagentSettings::fromArray([
            'agents' => [
                'agent-1' => ['contextTier' => 'inherit', 'effortLevel' => 'low'],
                'agent-2' => ['model' => 'gpt-5'],
            ],
        ]);

        expect($settings->agents)->toHaveCount(2)
            ->and($settings->agents['agent-1'])->toBeInstanceOf(SubagentSettingsEntry::class);
    });

    it('handles empty agents', function () {
        $settings = SubagentSettings::fromArray([]);

        expect($settings->agents)->toBeNull();
    });

    it('converts to array', function () {
        $settings = SubagentSettings::fromArray([
            'agents' => ['agent-a' => ['effortLevel' => 'low']],
        ]);

        expect($settings->toArray())->toHaveKey('agents');
    });
});

describe('UpdateSubagentSettingsRequest', function () {
    it('can be created with subagents', function () {
        $req = UpdateSubagentSettingsRequest::fromArray([
            'subagents' => [
                'agents' => ['agent-a' => ['effortLevel' => 'medium']],
            ],
        ]);

        expect($req->subagents)->toBeInstanceOf(SubagentSettings::class)
            ->and($req->subagents->agents['agent-a']->effortLevel)->toBe('medium');
    });

    it('handles null subagents', function () {
        $req = UpdateSubagentSettingsRequest::fromArray([]);

        expect($req->subagents)->toBeNull();
    });

    it('converts to array', function () {
        $settings = new SubagentSettings(agents: []);
        $req = new UpdateSubagentSettingsRequest(subagents: $settings);

        expect($req->toArray())->toHaveKey('subagents');
    });
});

describe('ToolsUpdateSubagentSettingsResult', function () {
    it('can be created from array', function () {
        $result = ToolsUpdateSubagentSettingsResult::fromArray([]);

        expect($result)->toBeInstanceOf(ToolsUpdateSubagentSettingsResult::class);
    });

    it('converts to empty array', function () {
        $result = new ToolsUpdateSubagentSettingsResult;

        expect($result->toArray())->toBeEmpty();
    });
});
