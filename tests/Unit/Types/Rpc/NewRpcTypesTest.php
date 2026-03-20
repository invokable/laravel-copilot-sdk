<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ElicitationAction;
use Revolution\Copilot\Enums\ExtensionSource;
use Revolution\Copilot\Enums\ExtensionStatus;
use Revolution\Copilot\Enums\McpServerStatus;
use Revolution\Copilot\Types\Rpc\ExtensionInfo;
use Revolution\Copilot\Types\Rpc\McpServerInfo;
use Revolution\Copilot\Types\Rpc\PluginInfo;
use Revolution\Copilot\Types\Rpc\SessionAgentReloadResult;
use Revolution\Copilot\Types\Rpc\SessionCommandsHandlePendingCommandParams;
use Revolution\Copilot\Types\Rpc\SessionCommandsHandlePendingCommandResult;
use Revolution\Copilot\Types\Rpc\SessionExtensionsDisableParams;
use Revolution\Copilot\Types\Rpc\SessionExtensionsEnableParams;
use Revolution\Copilot\Types\Rpc\SessionExtensionsListResult;
use Revolution\Copilot\Types\Rpc\SessionMcpDisableParams;
use Revolution\Copilot\Types\Rpc\SessionMcpEnableParams;
use Revolution\Copilot\Types\Rpc\SessionMcpListResult;
use Revolution\Copilot\Types\Rpc\SessionPluginsListResult;
use Revolution\Copilot\Types\Rpc\SessionSkillsDisableParams;
use Revolution\Copilot\Types\Rpc\SessionSkillsEnableParams;
use Revolution\Copilot\Types\Rpc\SessionSkillsListResult;
use Revolution\Copilot\Types\Rpc\SessionUiElicitationParams;
use Revolution\Copilot\Types\Rpc\SessionUiElicitationResult;
use Revolution\Copilot\Types\Rpc\SkillInfo;

describe('SkillInfo', function () {
    it('can be created from array', function () {
        $info = SkillInfo::fromArray([
            'name' => 'code-review',
            'description' => 'Reviews code changes',
            'source' => 'project',
            'userInvocable' => true,
            'enabled' => true,
            'path' => '/workspace/.copilot/skills/code-review',
        ]);

        expect($info->name)->toBe('code-review')
            ->and($info->description)->toBe('Reviews code changes')
            ->and($info->source)->toBe('project')
            ->and($info->userInvocable)->toBeTrue()
            ->and($info->enabled)->toBeTrue()
            ->and($info->path)->toBe('/workspace/.copilot/skills/code-review');
    });

    it('can be created from array without optional path', function () {
        $info = SkillInfo::fromArray([
            'name' => 'testing',
            'description' => 'Runs tests',
            'source' => 'personal',
            'userInvocable' => false,
            'enabled' => false,
        ]);

        expect($info->path)->toBeNull();
    });

    it('can convert to array', function () {
        $info = new SkillInfo(
            name: 'code-review',
            description: 'Reviews code',
            source: 'project',
            userInvocable: true,
            enabled: true,
        );

        expect($info->toArray())->toBe([
            'name' => 'code-review',
            'description' => 'Reviews code',
            'source' => 'project',
            'userInvocable' => true,
            'enabled' => true,
        ]);
    });

    it('includes path in toArray when set', function () {
        $info = new SkillInfo(
            name: 'test',
            description: 'Testing',
            source: 'project',
            userInvocable: true,
            enabled: true,
            path: '/skills/test',
        );

        expect($info->toArray())->toHaveKey('path', '/skills/test');
    });

    it('implements Arrayable interface', function () {
        $info = new SkillInfo(name: 'a', description: 'b', source: 'c', userInvocable: true, enabled: true);
        expect($info)->toBeInstanceOf(Arrayable::class);
    });
});

describe('McpServerInfo', function () {
    it('can be created from array', function () {
        $info = McpServerInfo::fromArray([
            'name' => 'github',
            'status' => 'connected',
            'source' => 'workspace',
            'error' => null,
        ]);

        expect($info->name)->toBe('github')
            ->and($info->status)->toBe(McpServerStatus::CONNECTED)
            ->and($info->source)->toBe('workspace');
    });

    it('can be created from array with error', function () {
        $info = McpServerInfo::fromArray([
            'name' => 'broken-server',
            'status' => 'failed',
            'error' => 'Connection refused',
        ]);

        expect($info->status)->toBe(McpServerStatus::FAILED)
            ->and($info->error)->toBe('Connection refused');
    });

    it('can be created from array with minimal fields', function () {
        $info = McpServerInfo::fromArray([
            'name' => 'pending-server',
            'status' => 'pending',
        ]);

        expect($info->source)->toBeNull()
            ->and($info->error)->toBeNull();
    });

    it('can convert to array', function () {
        $info = new McpServerInfo(
            name: 'github',
            status: McpServerStatus::CONNECTED,
            source: 'workspace',
        );

        expect($info->toArray())->toBe([
            'name' => 'github',
            'status' => 'connected',
            'source' => 'workspace',
        ]);
    });

    it('implements Arrayable interface', function () {
        $info = new McpServerInfo(name: 'a', status: McpServerStatus::PENDING);
        expect($info)->toBeInstanceOf(Arrayable::class);
    });
});

describe('PluginInfo', function () {
    it('can be created from array', function () {
        $info = PluginInfo::fromArray([
            'name' => 'eslint',
            'marketplace' => 'npm',
            'enabled' => true,
            'version' => '8.0.0',
        ]);

        expect($info->name)->toBe('eslint')
            ->and($info->marketplace)->toBe('npm')
            ->and($info->enabled)->toBeTrue()
            ->and($info->version)->toBe('8.0.0');
    });

    it('can be created from array without version', function () {
        $info = PluginInfo::fromArray([
            'name' => 'prettier',
            'marketplace' => 'npm',
            'enabled' => false,
        ]);

        expect($info->version)->toBeNull();
    });

    it('can convert to array', function () {
        $info = new PluginInfo(name: 'eslint', marketplace: 'npm', enabled: true, version: '8.0.0');

        expect($info->toArray())->toBe([
            'name' => 'eslint',
            'marketplace' => 'npm',
            'enabled' => true,
            'version' => '8.0.0',
        ]);
    });

    it('filters null version in toArray', function () {
        $info = new PluginInfo(name: 'eslint', marketplace: 'npm', enabled: true);

        expect($info->toArray())->not->toHaveKey('version');
    });

    it('implements Arrayable interface', function () {
        $info = new PluginInfo(name: 'a', marketplace: 'b', enabled: true);
        expect($info)->toBeInstanceOf(Arrayable::class);
    });
});

describe('ExtensionInfo', function () {
    it('can be created from array', function () {
        $info = ExtensionInfo::fromArray([
            'id' => 'project:my-ext',
            'name' => 'my-ext',
            'source' => 'project',
            'status' => 'running',
            'pid' => 12345,
        ]);

        expect($info->id)->toBe('project:my-ext')
            ->and($info->name)->toBe('my-ext')
            ->and($info->source)->toBe(ExtensionSource::PROJECT)
            ->and($info->status)->toBe(ExtensionStatus::RUNNING)
            ->and($info->pid)->toBe(12345);
    });

    it('can be created from array without pid', function () {
        $info = ExtensionInfo::fromArray([
            'id' => 'user:auth-helper',
            'name' => 'auth-helper',
            'source' => 'user',
            'status' => 'disabled',
        ]);

        expect($info->source)->toBe(ExtensionSource::USER)
            ->and($info->status)->toBe(ExtensionStatus::DISABLED)
            ->and($info->pid)->toBeNull();
    });

    it('can convert to array', function () {
        $info = new ExtensionInfo(
            id: 'project:my-ext',
            name: 'my-ext',
            source: ExtensionSource::PROJECT,
            status: ExtensionStatus::RUNNING,
            pid: 42,
        );

        expect($info->toArray())->toBe([
            'id' => 'project:my-ext',
            'name' => 'my-ext',
            'source' => 'project',
            'status' => 'running',
            'pid' => 42,
        ]);
    });

    it('filters null pid in toArray', function () {
        $info = new ExtensionInfo(
            id: 'project:test',
            name: 'test',
            source: ExtensionSource::PROJECT,
            status: ExtensionStatus::STARTING,
        );

        expect($info->toArray())->not->toHaveKey('pid');
    });

    it('implements Arrayable interface', function () {
        $info = new ExtensionInfo(id: 'a', name: 'b', source: ExtensionSource::USER, status: ExtensionStatus::FAILED);
        expect($info)->toBeInstanceOf(Arrayable::class);
    });
});

describe('SessionSkillsListResult', function () {
    it('can be created from array', function () {
        $result = SessionSkillsListResult::fromArray([
            'skills' => [
                [
                    'name' => 'code-review',
                    'description' => 'Reviews code',
                    'source' => 'project',
                    'userInvocable' => true,
                    'enabled' => true,
                ],
            ],
        ]);

        expect($result->skills)->toHaveCount(1)
            ->and($result->skills[0])->toBeInstanceOf(SkillInfo::class)
            ->and($result->skills[0]->name)->toBe('code-review');
    });

    it('handles empty skills list', function () {
        $result = SessionSkillsListResult::fromArray([]);
        expect($result->skills)->toBe([]);
    });

    it('can convert to array', function () {
        $result = new SessionSkillsListResult(skills: [
            new SkillInfo(name: 'test', description: 'Testing', source: 'project', userInvocable: true, enabled: true),
        ]);

        $array = $result->toArray();
        expect($array['skills'])->toHaveCount(1)
            ->and($array['skills'][0]['name'])->toBe('test');
    });
});

describe('SessionSkillsEnableParams', function () {
    it('can be created and converted', function () {
        $params = new SessionSkillsEnableParams(name: 'code-review');
        expect($params->toArray())->toBe(['name' => 'code-review']);
    });

    it('can be created from array', function () {
        $params = SessionSkillsEnableParams::fromArray(['name' => 'testing']);
        expect($params->name)->toBe('testing');
    });
});

describe('SessionSkillsDisableParams', function () {
    it('can be created and converted', function () {
        $params = new SessionSkillsDisableParams(name: 'code-review');
        expect($params->toArray())->toBe(['name' => 'code-review']);
    });

    it('can be created from array', function () {
        $params = SessionSkillsDisableParams::fromArray(['name' => 'testing']);
        expect($params->name)->toBe('testing');
    });
});

describe('SessionMcpListResult', function () {
    it('can be created from array', function () {
        $result = SessionMcpListResult::fromArray([
            'servers' => [
                [
                    'name' => 'github',
                    'status' => 'connected',
                    'source' => 'workspace',
                ],
            ],
        ]);

        expect($result->servers)->toHaveCount(1)
            ->and($result->servers[0])->toBeInstanceOf(McpServerInfo::class)
            ->and($result->servers[0]->name)->toBe('github');
    });

    it('handles empty servers list', function () {
        $result = SessionMcpListResult::fromArray([]);
        expect($result->servers)->toBe([]);
    });

    it('can convert to array', function () {
        $result = new SessionMcpListResult(servers: [
            new McpServerInfo(name: 'github', status: McpServerStatus::CONNECTED),
        ]);

        $array = $result->toArray();
        expect($array['servers'])->toHaveCount(1)
            ->and($array['servers'][0]['name'])->toBe('github');
    });
});

describe('SessionMcpEnableParams', function () {
    it('can be created and converted', function () {
        $params = new SessionMcpEnableParams(serverName: 'github');
        expect($params->toArray())->toBe(['serverName' => 'github']);
    });

    it('can be created from array', function () {
        $params = SessionMcpEnableParams::fromArray(['serverName' => 'slack']);
        expect($params->serverName)->toBe('slack');
    });
});

describe('SessionMcpDisableParams', function () {
    it('can be created and converted', function () {
        $params = new SessionMcpDisableParams(serverName: 'github');
        expect($params->toArray())->toBe(['serverName' => 'github']);
    });

    it('can be created from array', function () {
        $params = SessionMcpDisableParams::fromArray(['serverName' => 'slack']);
        expect($params->serverName)->toBe('slack');
    });
});

describe('SessionPluginsListResult', function () {
    it('can be created from array', function () {
        $result = SessionPluginsListResult::fromArray([
            'plugins' => [
                [
                    'name' => 'eslint',
                    'marketplace' => 'npm',
                    'enabled' => true,
                    'version' => '8.0.0',
                ],
            ],
        ]);

        expect($result->plugins)->toHaveCount(1)
            ->and($result->plugins[0])->toBeInstanceOf(PluginInfo::class)
            ->and($result->plugins[0]->name)->toBe('eslint');
    });

    it('handles empty plugins list', function () {
        $result = SessionPluginsListResult::fromArray([]);
        expect($result->plugins)->toBe([]);
    });

    it('can convert to array', function () {
        $result = new SessionPluginsListResult(plugins: [
            new PluginInfo(name: 'eslint', marketplace: 'npm', enabled: true),
        ]);

        $array = $result->toArray();
        expect($array['plugins'])->toHaveCount(1)
            ->and($array['plugins'][0]['name'])->toBe('eslint');
    });
});

describe('SessionExtensionsListResult', function () {
    it('can be created from array', function () {
        $result = SessionExtensionsListResult::fromArray([
            'extensions' => [
                [
                    'id' => 'project:my-ext',
                    'name' => 'my-ext',
                    'source' => 'project',
                    'status' => 'running',
                ],
            ],
        ]);

        expect($result->extensions)->toHaveCount(1)
            ->and($result->extensions[0])->toBeInstanceOf(ExtensionInfo::class)
            ->and($result->extensions[0]->id)->toBe('project:my-ext');
    });

    it('handles empty extensions list', function () {
        $result = SessionExtensionsListResult::fromArray([]);
        expect($result->extensions)->toBe([]);
    });

    it('can convert to array', function () {
        $result = new SessionExtensionsListResult(extensions: [
            new ExtensionInfo(id: 'project:test', name: 'test', source: ExtensionSource::PROJECT, status: ExtensionStatus::RUNNING),
        ]);

        $array = $result->toArray();
        expect($array['extensions'])->toHaveCount(1)
            ->and($array['extensions'][0]['id'])->toBe('project:test');
    });
});

describe('SessionExtensionsEnableParams', function () {
    it('can be created and converted', function () {
        $params = new SessionExtensionsEnableParams(id: 'project:my-ext');
        expect($params->toArray())->toBe(['id' => 'project:my-ext']);
    });

    it('can be created from array', function () {
        $params = SessionExtensionsEnableParams::fromArray(['id' => 'user:auth-helper']);
        expect($params->id)->toBe('user:auth-helper');
    });
});

describe('SessionExtensionsDisableParams', function () {
    it('can be created and converted', function () {
        $params = new SessionExtensionsDisableParams(id: 'project:my-ext');
        expect($params->toArray())->toBe(['id' => 'project:my-ext']);
    });

    it('can be created from array', function () {
        $params = SessionExtensionsDisableParams::fromArray(['id' => 'user:auth-helper']);
        expect($params->id)->toBe('user:auth-helper');
    });
});

describe('SessionCommandsHandlePendingCommandParams', function () {
    it('can be created with requestId only', function () {
        $params = new SessionCommandsHandlePendingCommandParams(requestId: 'req-123');
        expect($params->requestId)->toBe('req-123')
            ->and($params->error)->toBeNull();
    });

    it('can be created with error', function () {
        $params = new SessionCommandsHandlePendingCommandParams(requestId: 'req-123', error: 'Command failed');
        expect($params->toArray())->toBe(['requestId' => 'req-123', 'error' => 'Command failed']);
    });

    it('filters null error in toArray', function () {
        $params = new SessionCommandsHandlePendingCommandParams(requestId: 'req-123');
        expect($params->toArray())->toBe(['requestId' => 'req-123']);
    });

    it('can be created from array', function () {
        $params = SessionCommandsHandlePendingCommandParams::fromArray([
            'requestId' => 'req-456',
            'error' => 'Something went wrong',
        ]);
        expect($params->requestId)->toBe('req-456')
            ->and($params->error)->toBe('Something went wrong');
    });

    it('can be created from array without error', function () {
        $params = SessionCommandsHandlePendingCommandParams::fromArray(['requestId' => 'req-789']);
        expect($params->requestId)->toBe('req-789')
            ->and($params->error)->toBeNull();
    });
});

describe('SessionCommandsHandlePendingCommandResult', function () {
    it('can be created from array', function () {
        $result = SessionCommandsHandlePendingCommandResult::fromArray(['success' => true]);
        expect($result->success)->toBeTrue();
    });

    it('can convert to array', function () {
        $result = new SessionCommandsHandlePendingCommandResult(success: false);
        expect($result->toArray())->toBe(['success' => false]);
    });
});

describe('SessionUiElicitationParams', function () {
    it('can be created and converted', function () {
        $params = new SessionUiElicitationParams(
            message: 'Enter your name',
            requestedSchema: ['type' => 'object', 'properties' => ['name' => ['type' => 'string']]],
        );

        expect($params->toArray())->toBe([
            'message' => 'Enter your name',
            'requestedSchema' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string']]],
        ]);
    });

    it('can be created from array', function () {
        $params = SessionUiElicitationParams::fromArray([
            'message' => 'Confirm action',
            'requestedSchema' => ['type' => 'boolean'],
        ]);

        expect($params->message)->toBe('Confirm action')
            ->and($params->requestedSchema)->toBe(['type' => 'boolean']);
    });
});

describe('SessionUiElicitationResult', function () {
    it('can be created with accept action and content', function () {
        $result = SessionUiElicitationResult::fromArray([
            'action' => 'accept',
            'content' => ['name' => 'John'],
        ]);

        expect($result->action)->toBe(ElicitationAction::ACCEPT)
            ->and($result->content)->toBe(['name' => 'John']);
    });

    it('can be created with decline action', function () {
        $result = SessionUiElicitationResult::fromArray(['action' => 'decline']);

        expect($result->action)->toBe(ElicitationAction::DECLINE)
            ->and($result->content)->toBeNull();
    });

    it('can be created with cancel action', function () {
        $result = SessionUiElicitationResult::fromArray(['action' => 'cancel']);

        expect($result->action)->toBe(ElicitationAction::CANCEL)
            ->and($result->content)->toBeNull();
    });

    it('can convert to array with content', function () {
        $result = new SessionUiElicitationResult(
            action: ElicitationAction::ACCEPT,
            content: ['key' => 'value'],
        );

        expect($result->toArray())->toBe([
            'action' => 'accept',
            'content' => ['key' => 'value'],
        ]);
    });

    it('filters null content in toArray', function () {
        $result = new SessionUiElicitationResult(action: ElicitationAction::DECLINE);

        expect($result->toArray())->toBe(['action' => 'decline']);
    });
});

describe('SessionAgentReloadResult', function () {
    it('can be created from array', function () {
        $result = SessionAgentReloadResult::fromArray([
            'agents' => [
                [
                    'name' => 'custom-agent',
                    'displayName' => 'Custom Agent',
                    'description' => 'A reloaded agent',
                ],
            ],
        ]);

        expect($result->agents)->toHaveCount(1)
            ->and($result->agents[0]->name)->toBe('custom-agent');
    });

    it('handles empty agents list', function () {
        $result = SessionAgentReloadResult::fromArray([]);
        expect($result->agents)->toBe([]);
    });

    it('can convert to array', function () {
        $result = SessionAgentReloadResult::fromArray([
            'agents' => [
                [
                    'name' => 'agent1',
                    'displayName' => 'Agent 1',
                    'description' => 'First agent',
                ],
            ],
        ]);

        $array = $result->toArray();
        expect($array['agents'])->toHaveCount(1)
            ->and($array['agents'][0]['name'])->toBe('agent1');
    });
});
