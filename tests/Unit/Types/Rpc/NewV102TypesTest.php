<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AgentDiscoveryPathScope;
use Revolution\Copilot\Enums\InstructionDiscoveryPathKind;
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
            'maxEntries' => 100,
        ]);

        expect($mem->enabled)->toBeTrue()
            ->and($mem->maxEntries)->toBe(100);
    });

    it('handles null fields', function () {
        $mem = MemoryConfiguration::fromArray([]);

        expect($mem->enabled)->toBeNull()
            ->and($mem->maxEntries)->toBeNull();
    });

    it('converts to array without null values', function () {
        $mem = new MemoryConfiguration(enabled: true, maxEntries: 50);

        $arr = $mem->toArray();

        expect($arr)->toHaveKey('enabled', true)
            ->and($arr)->toHaveKey('maxEntries', 50);
    });

    it('implements Arrayable interface', function () {
        expect(new MemoryConfiguration)->toBeInstanceOf(Arrayable::class);
    });
});

describe('NamedProviderConfig', function () {
    it('can be created with all fields', function () {
        $provider = NamedProviderConfig::fromArray([
            'id' => 'openai-custom',
            'type' => 'openai',
            'apiKey' => 'sk-test',
            'apiUrl' => 'https://api.openai.com/v1',
        ]);

        expect($provider->id)->toBe('openai-custom')
            ->and($provider->type)->toBe('openai')
            ->and($provider->apiKey)->toBe('sk-test')
            ->and($provider->apiUrl)->toBe('https://api.openai.com/v1');
    });

    it('handles default values', function () {
        $provider = NamedProviderConfig::fromArray(['id' => 'my-provider', 'type' => 'openai']);

        expect($provider->apiKey)->toBeNull()
            ->and($provider->apiUrl)->toBeNull();
    });

    it('converts to array', function () {
        $provider = new NamedProviderConfig(id: 'test', type: 'openai');

        expect($provider->toArray())->toHaveKey('id', 'test')
            ->and($provider->toArray())->toHaveKey('type', 'openai');
    });

    it('implements Arrayable interface', function () {
        expect(new NamedProviderConfig(id: 'x', type: 'openai'))->toBeInstanceOf(Arrayable::class);
    });
});

describe('ProviderModelConfig', function () {
    it('can be created with all fields', function () {
        $model = ProviderModelConfig::fromArray([
            'id' => 'gpt-5',
            'providerId' => 'openai-custom',
            'modelId' => 'gpt-5-latest',
        ]);

        expect($model->id)->toBe('gpt-5')
            ->and($model->providerId)->toBe('openai-custom')
            ->and($model->modelId)->toBe('gpt-5-latest');
    });

    it('handles optional fields', function () {
        $model = ProviderModelConfig::fromArray(['id' => 'm1', 'providerId' => 'p1']);

        expect($model->modelId)->toBeNull();
    });

    it('converts to array', function () {
        $model = new ProviderModelConfig(id: 'm1', providerId: 'p1', modelId: 'gpt-5');

        expect($model->toArray())->toHaveKey('id', 'm1')
            ->and($model->toArray())->toHaveKey('providerId', 'p1')
            ->and($model->toArray())->toHaveKey('modelId', 'gpt-5');
    });
});

describe('ModelBillingTokenPricesLongContext', function () {
    it('can be created from array', function () {
        $lc = ModelBillingTokenPricesLongContext::fromArray([
            'inputMTokenPrice' => 5.0,
            'outputMTokenPrice' => 15.0,
        ]);

        expect($lc->inputMTokenPrice)->toBe(5.0)
            ->and($lc->outputMTokenPrice)->toBe(15.0);
    });

    it('handles null values', function () {
        $lc = ModelBillingTokenPricesLongContext::fromArray([]);

        expect($lc->inputMTokenPrice)->toBeNull()
            ->and($lc->outputMTokenPrice)->toBeNull();
    });

    it('converts to array', function () {
        $lc = new ModelBillingTokenPricesLongContext(inputMTokenPrice: 3.0, outputMTokenPrice: 10.0);

        expect($lc->toArray())->toHaveKey('inputMTokenPrice', 3.0)
            ->and($lc->toArray())->toHaveKey('outputMTokenPrice', 10.0);
    });
});

describe('ModelBillingTokenPrices', function () {
    it('can be created from array with all fields', function () {
        $prices = ModelBillingTokenPrices::fromArray([
            'inputMTokenPrice' => 2.5,
            'outputMTokenPrice' => 7.5,
            'longContext' => [
                'inputMTokenPrice' => 5.0,
                'outputMTokenPrice' => 15.0,
            ],
        ]);

        expect($prices->inputMTokenPrice)->toBe(2.5)
            ->and($prices->outputMTokenPrice)->toBe(7.5)
            ->and($prices->longContext)->toBeInstanceOf(ModelBillingTokenPricesLongContext::class)
            ->and($prices->longContext->inputMTokenPrice)->toBe(5.0);
    });

    it('handles missing fields', function () {
        $prices = ModelBillingTokenPrices::fromArray([]);

        expect($prices->inputMTokenPrice)->toBeNull()
            ->and($prices->longContext)->toBeNull();
    });

    it('converts to array', function () {
        $prices = new ModelBillingTokenPrices(inputMTokenPrice: 1.0, outputMTokenPrice: 3.0);

        expect($prices->toArray())->toHaveKey('inputMTokenPrice', 1.0)
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
            ->and($path->scope)->toBe('user')
            ->and($path->preferredForCreation)->toBeTrue();
    });

    it('handles default values', function () {
        $path = AgentDiscoveryPath::fromArray(['path' => '/some/path', 'scope' => 'project']);

        expect($path->preferredForCreation)->toBeFalse();
    });

    it('converts to array including false preferredForCreation', function () {
        $path = new AgentDiscoveryPath(path: '/some/path', scope: 'project', preferredForCreation: false);

        $arr = $path->toArray();
        expect($arr)->toHaveKey('path', '/some/path')
            ->and($arr)->toHaveKey('scope', 'project')
            ->and($arr)->toHaveKey('preferredForCreation', false);
    });

    it('implements Arrayable interface', function () {
        expect(new AgentDiscoveryPath(path: '/p', scope: 'user'))->toBeInstanceOf(Arrayable::class);
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
            ->and($list->paths[0]->scope)->toBe('user');
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

        expect($req->scope)->toBeNull();
    });

    it('can be created with scope', function () {
        $req = AgentsGetDiscoveryPathsRequest::fromArray(['scope' => 'user']);

        expect($req->scope)->toBe('user');
    });

    it('converts to array without null values', function () {
        $req = new AgentsGetDiscoveryPathsRequest(scope: null);

        expect($req->toArray())->toBeEmpty();
    });
});

describe('SkillDiscoveryPath', function () {
    it('can be created from array with all fields', function () {
        $path = SkillDiscoveryPath::fromArray([
            'path' => '/home/user/.copilot/skills',
            'scope' => 'user',
            'preferredForCreation' => true,
        ]);

        expect($path->path)->toBe('/home/user/.copilot/skills')
            ->and($path->scope)->toBe('user')
            ->and($path->preferredForCreation)->toBeTrue();
    });

    it('converts to array including false preferredForCreation', function () {
        $path = new SkillDiscoveryPath(path: '/p', scope: 'project', preferredForCreation: false);

        expect($path->toArray())->toHaveKey('preferredForCreation', false);
    });
});

describe('SkillDiscoveryPathList', function () {
    it('can be created from array with paths', function () {
        $list = SkillDiscoveryPathList::fromArray([
            'paths' => [
                ['path' => '/home/user/.copilot/skills', 'scope' => 'user', 'preferredForCreation' => true],
            ],
        ]);

        expect($list->paths)->toHaveCount(1)
            ->and($list->paths[0])->toBeInstanceOf(SkillDiscoveryPath::class);
    });
});

describe('SkillsGetDiscoveryPathsRequest', function () {
    it('can be created with scope', function () {
        $req = SkillsGetDiscoveryPathsRequest::fromArray(['scope' => 'project']);

        expect($req->scope)->toBe('project');
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
            ->and($path->location)->toBe('user')
            ->and($path->kind)->toBe('file')
            ->and($path->preferredForCreation)->toBeTrue();
    });

    it('converts to array with false preferredForCreation', function () {
        $path = new InstructionDiscoveryPath(path: '/p', location: 'repository', kind: 'file', preferredForCreation: false);

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
    it('can be created with location', function () {
        $req = InstructionsGetDiscoveryPathsRequest::fromArray(['location' => 'repository']);

        expect($req->location)->toBe('repository');
    });

    it('converts to empty array when null', function () {
        $req = new InstructionsGetDiscoveryPathsRequest;

        expect($req->toArray())->toBeEmpty();
    });
});

describe('PlanSqlTodoDependency', function () {
    it('can be created from array', function () {
        $dep = PlanSqlTodoDependency::fromArray([
            'todo_id' => 'task-1',
            'depends_on' => 'task-2',
        ]);

        expect($dep->todo_id)->toBe('task-1')
            ->and($dep->depends_on)->toBe('task-2');
    });

    it('converts to array', function () {
        $dep = new PlanSqlTodoDependency(todo_id: 'a', depends_on: 'b');

        expect($dep->toArray())->toBe(['todo_id' => 'a', 'depends_on' => 'b']);
    });
});

describe('PlanReadSqlTodosWithDependenciesResult', function () {
    it('can be created from array with rows and dependencies', function () {
        $result = PlanReadSqlTodosWithDependenciesResult::fromArray([
            'rows' => [
                ['id' => 1, 'title' => 'Task 1', 'status' => 'pending'],
            ],
            'dependencies' => [
                ['todo_id' => 'task-1', 'depends_on' => 'task-2'],
            ],
        ]);

        expect($result->rows)->toHaveCount(1)
            ->and($result->dependencies)->toHaveCount(1)
            ->and($result->dependencies[0])->toBeInstanceOf(PlanSqlTodoDependency::class)
            ->and($result->dependencies[0]->todo_id)->toBe('task-1');
    });

    it('handles empty result', function () {
        $result = PlanReadSqlTodosWithDependenciesResult::fromArray([]);

        expect($result->rows)->toBeEmpty()
            ->and($result->dependencies)->toBeEmpty();
    });

    it('converts to array', function () {
        $result = PlanReadSqlTodosWithDependenciesResult::fromArray([
            'rows' => [['id' => 1]],
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
        $token = new ProviderSessionToken(token: 'test-token');

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
    it('can be created with model', function () {
        $req = ProviderGetEndpointRequest::fromArray(['model' => 'gpt-5']);

        expect($req->model)->toBe('gpt-5');
    });

    it('handles no model', function () {
        $req = ProviderGetEndpointRequest::fromArray([]);

        expect($req->model)->toBeNull();
    });

    it('converts to empty array when null', function () {
        $req = new ProviderGetEndpointRequest;

        expect($req->toArray())->toBeEmpty();
    });
});

describe('SubagentSettingsEntry', function () {
    it('can be created from array with all fields', function () {
        $entry = SubagentSettingsEntry::fromArray([
            'agentName' => 'my-agent',
            'contextTier' => 'medium',
            'enabled' => true,
        ]);

        expect($entry->agentName)->toBe('my-agent')
            ->and($entry->contextTier)->toBe('medium')
            ->and($entry->enabled)->toBeTrue();
    });

    it('handles optional fields', function () {
        $entry = SubagentSettingsEntry::fromArray(['agentName' => 'agent']);

        expect($entry->contextTier)->toBeNull()
            ->and($entry->enabled)->toBeNull();
    });

    it('converts to array', function () {
        $entry = new SubagentSettingsEntry(agentName: 'agent', contextTier: 'high', enabled: true);

        expect($entry->toArray())->toHaveKey('agentName', 'agent')
            ->and($entry->toArray())->toHaveKey('contextTier', 'high')
            ->and($entry->toArray())->toHaveKey('enabled', true);
    });
});

describe('SubagentSettings', function () {
    it('can be created from array with entries', function () {
        $settings = SubagentSettings::fromArray([
            'entries' => [
                ['agentName' => 'agent-1', 'contextTier' => 'low', 'enabled' => true],
                ['agentName' => 'agent-2', 'contextTier' => 'high', 'enabled' => false],
            ],
        ]);

        expect($settings->entries)->toHaveCount(2)
            ->and($settings->entries[0])->toBeInstanceOf(SubagentSettingsEntry::class)
            ->and($settings->entries[0]->agentName)->toBe('agent-1');
    });

    it('handles empty entries', function () {
        $settings = SubagentSettings::fromArray([]);

        expect($settings->entries)->toBeEmpty();
    });

    it('converts to array', function () {
        $settings = SubagentSettings::fromArray([
            'entries' => [['agentName' => 'a']],
        ]);

        expect($settings->toArray())->toHaveKey('entries');
    });
});

describe('UpdateSubagentSettingsRequest', function () {
    it('can be created with settings', function () {
        $req = UpdateSubagentSettingsRequest::fromArray([
            'settings' => [
                'entries' => [['agentName' => 'agent']],
            ],
        ]);

        expect($req->settings)->toBeInstanceOf(SubagentSettings::class)
            ->and($req->settings->entries[0]->agentName)->toBe('agent');
    });

    it('handles null settings', function () {
        $req = UpdateSubagentSettingsRequest::fromArray([]);

        expect($req->settings)->toBeNull();
    });

    it('converts to array', function () {
        $settings = new SubagentSettings(entries: []);
        $req = new UpdateSubagentSettingsRequest(settings: $settings);

        expect($req->toArray())->toHaveKey('settings');
    });
});

describe('ToolsUpdateSubagentSettingsResult', function () {
    it('can be created from array', function () {
        $result = ToolsUpdateSubagentSettingsResult::fromArray([
            'settings' => [
                'entries' => [['agentName' => 'agent-1', 'contextTier' => 'medium']],
            ],
        ]);

        expect($result->settings)->toBeInstanceOf(SubagentSettings::class)
            ->and($result->settings->entries)->toHaveCount(1);
    });

    it('handles null settings', function () {
        $result = ToolsUpdateSubagentSettingsResult::fromArray([]);

        expect($result->settings)->toBeNull();
    });

    it('converts to array', function () {
        $result = new ToolsUpdateSubagentSettingsResult(settings: null);

        expect($result->toArray())->toBeEmpty();
    });
});
