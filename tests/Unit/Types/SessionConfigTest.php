<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\Types\InfiniteSessionConfig;
use Revolution\Copilot\Types\ProviderConfig;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionHooks;
use Revolution\Copilot\Types\SystemMessageConfig;

describe('SessionConfig', function () {
    it('can be created from array with all fields', function () {
        $handler = fn () => true;
        $userInputHandler = fn () => ['answer' => 'test', 'wasFreeform' => false];
        $preToolUseHook = fn () => null;

        $config = SessionConfig::fromArray([
            'sessionId' => 'test-session-id',
            'clientName' => 'my-app',
            'model' => 'claude-sonnet-4.5',
            'configDir' => '/tmp/config',
            'tools' => [['name' => 'test_tool']],
            'systemMessage' => ['mode' => 'append', 'content' => 'Extra instructions'],
            'availableTools' => ['bash', 'view'],
            'excludedTools' => ['edit'],
            'provider' => ['baseUrl' => 'https://api.example.com'],
            'onPermissionRequest' => $handler,
            'onUserInputRequest' => $userInputHandler,
            'hooks' => ['onPreToolUse' => $preToolUseHook],
            'workingDirectory' => '/home/user/project',
            'streaming' => true,
            'mcpServers' => ['server1' => ['command' => 'npx']],
            'customAgents' => [['name' => 'agent1']],
            'skillDirectories' => ['/path/to/skills'],
            'disabledSkills' => ['skill1'],
            'infiniteSessions' => ['enabled' => true, 'backgroundCompactionThreshold' => 0.80],
            'agent' => 'reviewer',
        ]);

        expect($config->sessionId)->toBe('test-session-id')
            ->and($config->clientName)->toBe('my-app')
            ->and($config->model)->toBe('claude-sonnet-4.5')
            ->and($config->configDir)->toBe('/tmp/config')
            ->and($config->tools)->toBe([['name' => 'test_tool']])
            ->and($config->systemMessage)->toBeInstanceOf(SystemMessageConfig::class)
            ->and($config->availableTools)->toBe(['bash', 'view'])
            ->and($config->excludedTools)->toBe(['edit'])
            ->and($config->provider)->toBeInstanceOf(ProviderConfig::class)
            ->and($config->onPermissionRequest)->toBe($handler)
            ->and($config->onUserInputRequest)->toBe($userInputHandler)
            ->and($config->hooks)->toBeInstanceOf(SessionHooks::class)
            ->and($config->hooks->onPreToolUse)->toBe($preToolUseHook)
            ->and($config->workingDirectory)->toBe('/home/user/project')
            ->and($config->streaming)->toBeTrue()
            ->and($config->mcpServers)->toBe(['server1' => ['command' => 'npx']])
            ->and($config->customAgents)->toBe([['name' => 'agent1']])
            ->and($config->skillDirectories)->toBe(['/path/to/skills'])
            ->and($config->disabledSkills)->toBe(['skill1'])
            ->and($config->infiniteSessions)->toBeInstanceOf(InfiniteSessionConfig::class)
            ->and($config->infiniteSessions->enabled)->toBeTrue()
            ->and($config->infiniteSessions->backgroundCompactionThreshold)->toBe(0.80)
            ->and($config->agent)->toBe('reviewer');
    });

    it('can be created from array with minimal fields', function () {
        $config = SessionConfig::fromArray([]);

        expect($config->sessionId)->toBeNull()
            ->and($config->clientName)->toBeNull()
            ->and($config->model)->toBeNull()
            ->and($config->configDir)->toBeNull()
            ->and($config->tools)->toBeNull()
            ->and($config->systemMessage)->toBeNull()
            ->and($config->availableTools)->toBeNull()
            ->and($config->excludedTools)->toBeNull()
            ->and($config->provider)->toBeNull()
            ->and($config->onPermissionRequest)->toBeNull()
            ->and($config->onUserInputRequest)->toBeNull()
            ->and($config->hooks)->toBeNull()
            ->and($config->workingDirectory)->toBeNull()
            ->and($config->streaming)->toBeNull()
            ->and($config->mcpServers)->toBeNull()
            ->and($config->customAgents)->toBeNull()
            ->and($config->skillDirectories)->toBeNull()
            ->and($config->disabledSkills)->toBeNull()
            ->and($config->infiniteSessions)->toBeNull()
            ->and($config->agent)->toBeNull();
    });

    it('preserves SystemMessageConfig instance when passed directly', function () {
        $systemMessage = new SystemMessageConfig(mode: 'replace', content: 'Custom message');
        $config = SessionConfig::fromArray([
            'systemMessage' => $systemMessage,
        ]);

        expect($config->systemMessage)->toBe($systemMessage);
    });

    it('preserves ProviderConfig instance when passed directly', function () {
        $provider = new ProviderConfig(baseUrl: 'https://api.test.com');
        $config = SessionConfig::fromArray([
            'provider' => $provider,
        ]);

        expect($config->provider)->toBe($provider);
    });

    it('can convert to array with all fields', function () {
        $handler = fn () => true;
        $userInputHandler = fn () => ['answer' => 'test', 'wasFreeform' => false];
        $preToolUseHook = fn () => null;

        $config = new SessionConfig(
            sessionId: 'session-123',
            clientName: 'my-app',
            model: 'gpt-4',
            configDir: '/config',
            tools: [['name' => 'tool1']],
            systemMessage: new SystemMessageConfig(mode: 'append', content: 'Instructions'),
            availableTools: ['bash'],
            excludedTools: ['view'],
            provider: new ProviderConfig(baseUrl: 'https://api.test.com'),
            onPermissionRequest: $handler,
            onUserInputRequest: $userInputHandler,
            hooks: new SessionHooks(onPreToolUse: $preToolUseHook),
            workingDirectory: '/home/user',
            streaming: false,
            mcpServers: ['server1' => ['command' => 'test']],
            customAgents: [['name' => 'agent1']],
            skillDirectories: ['/skills'],
            disabledSkills: ['skill1'],
            infiniteSessions: new InfiniteSessionConfig(enabled: false),
            agent: 'reviewer',
        );

        $array = $config->toArray();

        expect($array['sessionId'])->toBe('session-123')
            ->and($array['clientName'])->toBe('my-app')
            ->and($array['model'])->toBe('gpt-4')
            ->and($array['configDir'])->toBe('/config')
            ->and($array['tools'])->toBe([['name' => 'tool1']])
            ->and($array['systemMessage'])->toBe(['mode' => 'append', 'content' => 'Instructions'])
            ->and($array['availableTools'])->toBe(['bash'])
            ->and($array['excludedTools'])->toBe(['view'])
            ->and($array['provider'])->toBe(['baseUrl' => 'https://api.test.com'])
            ->and($array['onPermissionRequest'])->toBe($handler)
            ->and($array['onUserInputRequest'])->toBe($userInputHandler)
            ->and($array['hooks'])->toBe(['onPreToolUse' => $preToolUseHook])
            ->and($array['workingDirectory'])->toBe('/home/user')
            ->and($array['streaming'])->toBeFalse()
            ->and($array['mcpServers'])->toBe(['server1' => ['command' => 'test']])
            ->and($array['customAgents'])->toBe([['name' => 'agent1']])
            ->and($array['skillDirectories'])->toBe(['/skills'])
            ->and($array['disabledSkills'])->toBe(['skill1'])
            ->and($array['infiniteSessions'])->toBe(['enabled' => false])
            ->and($array['agent'])->toBe('reviewer');
    });

    it('filters null values in toArray', function () {
        $config = new SessionConfig;

        expect($config->toArray())->toBe([]);
    });

    it('handles systemMessage as array in toArray', function () {
        $config = new SessionConfig(
            systemMessage: ['mode' => 'replace', 'content' => 'Custom'],
        );

        expect($config->toArray()['systemMessage'])->toBe([
            'mode' => 'replace',
            'content' => 'Custom',
        ]);
    });

    it('handles provider as array in toArray', function () {
        $config = new SessionConfig(
            provider: ['baseUrl' => 'https://api.example.com', 'type' => 'openai'],
        );

        expect($config->toArray()['provider'])->toBe([
            'baseUrl' => 'https://api.example.com',
            'type' => 'openai',
        ]);
    });

    it('preserves InfiniteSessionConfig instance when passed directly', function () {
        $infiniteSessions = new InfiniteSessionConfig(enabled: true, backgroundCompactionThreshold: 0.75);
        $config = SessionConfig::fromArray([
            'infiniteSessions' => $infiniteSessions,
        ]);

        expect($config->infiniteSessions)->toBe($infiniteSessions);
    });

    it('handles infiniteSessions as array in toArray', function () {
        $config = new SessionConfig(
            infiniteSessions: ['enabled' => true, 'bufferExhaustionThreshold' => 0.90],
        );

        expect($config->toArray()['infiniteSessions'])->toBe([
            'enabled' => true,
            'bufferExhaustionThreshold' => 0.90,
        ]);
    });

    it('implements Arrayable interface', function () {
        $config = new SessionConfig;

        expect($config)->toBeInstanceOf(Arrayable::class);
    });

    it('accepts reasoningEffort as enum', function () {
        $config = new SessionConfig(
            reasoningEffort: ReasoningEffort::HIGH,
        );

        expect($config->reasoningEffort)->toBe(ReasoningEffort::HIGH);
    });

    it('accepts reasoningEffort as string', function () {
        $config = new SessionConfig(
            reasoningEffort: 'medium',
        );

        expect($config->reasoningEffort)->toBe('medium');
    });

    it('converts reasoningEffort enum to string in toArray', function () {
        $config = new SessionConfig(
            reasoningEffort: ReasoningEffort::XHIGH,
        );

        $array = $config->toArray();

        expect($array['reasoningEffort'])->toBe('xhigh');
    });

    it('preserves reasoningEffort string in toArray', function () {
        $config = new SessionConfig(
            reasoningEffort: 'low',
        );

        $array = $config->toArray();

        expect($array['reasoningEffort'])->toBe('low');
    });

    it('can be created from array with reasoningEffort as string', function () {
        $config = SessionConfig::fromArray([
            'reasoningEffort' => 'high',
        ]);

        expect($config->reasoningEffort)->toBe('high');
    });

    it('preserves reasoningEffort enum when passed to fromArray', function () {
        $config = SessionConfig::fromArray([
            'reasoningEffort' => ReasoningEffort::MEDIUM,
        ]);

        expect($config->reasoningEffort)->toBe(ReasoningEffort::MEDIUM);
    });

    it('accepts agent parameter', function () {
        $config = new SessionConfig(agent: 'reviewer');

        expect($config->agent)->toBe('reviewer');
    });

    it('includes agent in toArray', function () {
        $config = new SessionConfig(agent: 'code-review');

        expect($config->toArray()['agent'])->toBe('code-review');
    });

    it('can be created from array with agent', function () {
        $config = SessionConfig::fromArray(['agent' => 'my-agent']);

        expect($config->agent)->toBe('my-agent');
    });

    it('excludes agent from toArray when null', function () {
        $config = new SessionConfig;

        expect($config->toArray())->not->toHaveKey('agent');
    });

    it('accepts commands parameter', function () {
        $handler = fn () => null;
        $commands = [
            ['name' => 'deploy', 'handler' => $handler, 'description' => 'Deploy the app'],
        ];

        $config = new SessionConfig(commands: $commands);

        expect($config->commands)->toBe($commands);
    });

    it('includes commands in toArray', function () {
        $handler = fn () => null;
        $config = new SessionConfig(commands: [
            ['name' => 'deploy', 'handler' => $handler],
        ]);

        expect($config->toArray()['commands'])->toHaveCount(1)
            ->and($config->toArray()['commands'][0]['name'])->toBe('deploy');
    });

    it('can be created from array with commands', function () {
        $handler = fn () => null;
        $config = SessionConfig::fromArray([
            'commands' => [
                ['name' => 'test', 'handler' => $handler],
            ],
        ]);

        expect($config->commands)->toHaveCount(1)
            ->and($config->commands[0]['name'])->toBe('test');
    });

    it('excludes commands from toArray when null', function () {
        $config = new SessionConfig;

        expect($config->toArray())->not->toHaveKey('commands');
    });
});
