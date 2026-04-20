<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\Types\InfiniteSessionConfig;
use Revolution\Copilot\Types\ProviderConfig;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionHooks;
use Revolution\Copilot\Types\SystemMessageConfig;

describe('ResumeSessionConfig', function () {
    it('can be created from array with all fields', function () {
        $handler = fn () => true;
        $userInputHandler = fn () => ['answer' => 'test', 'wasFreeform' => false];
        $preToolUseHook = fn () => null;

        $config = ResumeSessionConfig::fromArray([
            'clientName' => 'my-app',
            'model' => 'claude-opus-4.7',
            'reasoningEffort' => ReasoningEffort::XHIGH,
            'configDir' => './src',
            'tools' => [['name' => 'test_tool']],
            'systemMessage' => new SystemMessageConfig(mode: 'append', content: 'Instructions'),
            'availableTools' => [],
            'excludedTools' => [],
            'provider' => ['baseUrl' => 'https://api.example.com'],
            'onPermissionRequest' => $handler,
            'onUserInputRequest' => $userInputHandler,
            'hooks' => ['onPreToolUse' => $preToolUseHook],
            'workingDirectory' => '/home/user/project',
            'streaming' => true,
            'includeSubAgentStreamingEvents' => true,
            'mcpServers' => ['server1' => ['command' => 'npx']],
            'customAgents' => [['name' => 'agent1']],
            'skillDirectories' => ['/path/to/skills'],
            'disabledSkills' => ['skill1'],
            'infiniteSessions' => new InfiniteSessionConfig(enabled: true, backgroundCompactionThreshold: 0.80, bufferExhaustionThreshold: 0.95),
            'disableResume' => true,
            'agent' => 'reviewer',
        ]);

        expect($config->tools)->toBe([['name' => 'test_tool']])
            ->and($config->clientName)->toBe('my-app')
            ->and($config->model)->toBe('claude-opus-4.7')
            ->and($config->reasoningEffort)->toBe(ReasoningEffort::XHIGH)
            ->and($config->systemMessage->content)->toBe('Instructions')
            ->and($config->provider)->toBeInstanceOf(ProviderConfig::class)
            ->and($config->onPermissionRequest)->toBe($handler)
            ->and($config->onUserInputRequest)->toBe($userInputHandler)
            ->and($config->hooks)->toBeInstanceOf(SessionHooks::class)
            ->and($config->hooks->onPreToolUse)->toBe($preToolUseHook)
            ->and($config->workingDirectory)->toBe('/home/user/project')
            ->and($config->disableResume)->toBeTrue()
            ->and($config->streaming)->toBeTrue()
            ->and($config->includeSubAgentStreamingEvents)->toBeTrue()
            ->and($config->mcpServers)->toBe(['server1' => ['command' => 'npx']])
            ->and($config->customAgents)->toBe([['name' => 'agent1']])
            ->and($config->skillDirectories)->toBe(['/path/to/skills'])
            ->and($config->disabledSkills)->toBe(['skill1'])
            ->and($config->agent)->toBe('reviewer');
    });

    it('can be created from array with minimal fields', function () {
        $config = ResumeSessionConfig::fromArray([]);

        expect($config->tools)->toBeNull()
            ->and($config->provider)->toBeNull()
            ->and($config->onPermissionRequest)->toBeNull()
            ->and($config->onUserInputRequest)->toBeNull()
            ->and($config->hooks)->toBeNull()
            ->and($config->workingDirectory)->toBeNull()
            ->and($config->disableResume)->toBeNull()
            ->and($config->streaming)->toBeNull()
            ->and($config->includeSubAgentStreamingEvents)->toBeNull()
            ->and($config->mcpServers)->toBeNull()
            ->and($config->customAgents)->toBeNull()
            ->and($config->skillDirectories)->toBeNull()
            ->and($config->disabledSkills)->toBeNull()
            ->and($config->agent)->toBeNull();
    });

    it('preserves ProviderConfig instance when passed directly', function () {
        $provider = new ProviderConfig(baseUrl: 'https://api.test.com');
        $config = ResumeSessionConfig::fromArray([
            'provider' => $provider,
        ]);

        expect($config->provider)->toBe($provider);
    });

    it('preserves SessionHooks instance when passed directly', function () {
        $hooks = new SessionHooks(onPreToolUse: fn () => null);
        $config = ResumeSessionConfig::fromArray([
            'hooks' => $hooks,
        ]);

        expect($config->hooks)->toBe($hooks);
    });

    it('can convert to array with all fields', function () {
        $handler = fn () => true;
        $userInputHandler = fn () => ['answer' => 'test', 'wasFreeform' => false];
        $preToolUseHook = fn () => null;

        $config = new ResumeSessionConfig(
            clientName: 'my-app',
            model: 'claude-opus-4.7',
            reasoningEffort: ReasoningEffort::XHIGH,
            configDir: './src',
            tools: [['name' => 'tool1']],
            systemMessage: new SystemMessageConfig(mode: 'append', content: 'Instructions'),
            availableTools: [],
            excludedTools: [],
            provider: new ProviderConfig(baseUrl: 'https://api.test.com'),
            onPermissionRequest: $handler,
            onUserInputRequest: $userInputHandler,
            hooks: new SessionHooks(onPreToolUse: $preToolUseHook),
            workingDirectory: '/home/user',
            streaming: true,
            includeSubAgentStreamingEvents: false,
            mcpServers: ['server1' => ['command' => 'test']],
            customAgents: [['name' => 'agent1']],
            skillDirectories: ['/skills'],
            disabledSkills: ['skill1'],
            infiniteSessions: new InfiniteSessionConfig(enabled: true, backgroundCompactionThreshold: 0.80, bufferExhaustionThreshold: 0.95),
            disableResume: false,
            agent: 'reviewer',
        );

        $array = $config->toArray();

        expect($array['tools'])->toBe([['name' => 'tool1']])
            ->and($array['clientName'])->toBe('my-app')
            ->and($array['provider'])->toBe(['baseUrl' => 'https://api.test.com'])
            ->and($array['onPermissionRequest'])->toBe($handler)
            ->and($array['onUserInputRequest'])->toBe($userInputHandler)
            ->and($array['hooks'])->toBe(['onPreToolUse' => $preToolUseHook])
            ->and($array['workingDirectory'])->toBe('/home/user')
            ->and($array['disableResume'])->toBeFalse()
            ->and($array['streaming'])->toBeTrue()
            ->and($array['includeSubAgentStreamingEvents'])->toBeFalse()
            ->and($array['mcpServers'])->toBe(['server1' => ['command' => 'test']])
            ->and($array['customAgents'])->toBe([['name' => 'agent1']])
            ->and($array['skillDirectories'])->toBe(['/skills'])
            ->and($array['disabledSkills'])->toBe(['skill1'])
            ->and($array['agent'])->toBe('reviewer');
    });

    it('filters null values in toArray', function () {
        $config = new ResumeSessionConfig;

        expect($config->toArray())->toBe([]);
    });

    it('handles provider as array in toArray', function () {
        $config = new ResumeSessionConfig(
            provider: ['baseUrl' => 'https://api.example.com', 'type' => 'openai'],
        );

        expect($config->toArray()['provider'])->toBe([
            'baseUrl' => 'https://api.example.com',
            'type' => 'openai',
        ]);
    });

    it('implements Arrayable interface', function () {
        $config = new ResumeSessionConfig;

        expect($config)->toBeInstanceOf(Arrayable::class);
    });

    it('accepts reasoningEffort as enum', function () {
        $config = new ResumeSessionConfig(
            reasoningEffort: ReasoningEffort::HIGH,
        );

        expect($config->reasoningEffort)->toBe(ReasoningEffort::HIGH);
    });

    it('accepts reasoningEffort as string', function () {
        $config = new ResumeSessionConfig(
            reasoningEffort: 'medium',
        );

        expect($config->reasoningEffort)->toBe('medium');
    });

    it('converts reasoningEffort enum to string in toArray', function () {
        $config = new ResumeSessionConfig(
            reasoningEffort: ReasoningEffort::XHIGH,
        );

        $array = $config->toArray();

        expect($array['reasoningEffort'])->toBe('xhigh');
    });

    it('preserves reasoningEffort string in toArray', function () {
        $config = new ResumeSessionConfig(
            reasoningEffort: 'low',
        );

        $array = $config->toArray();

        expect($array['reasoningEffort'])->toBe('low');
    });

    it('can be created from array with reasoningEffort as string', function () {
        $config = ResumeSessionConfig::fromArray([
            'reasoningEffort' => 'high',
        ]);

        expect($config->reasoningEffort)->toBe('high');
    });

    it('preserves reasoningEffort enum when passed to fromArray', function () {
        $config = ResumeSessionConfig::fromArray([
            'reasoningEffort' => ReasoningEffort::MEDIUM,
        ]);

        expect($config->reasoningEffort)->toBe(ReasoningEffort::MEDIUM);
    });

    it('accepts agent parameter', function () {
        $config = new ResumeSessionConfig(agent: 'reviewer');

        expect($config->agent)->toBe('reviewer');
    });

    it('includes agent in toArray', function () {
        $config = new ResumeSessionConfig(agent: 'code-review');

        expect($config->toArray()['agent'])->toBe('code-review');
    });

    it('can be created from array with agent', function () {
        $config = ResumeSessionConfig::fromArray(['agent' => 'my-agent']);

        expect($config->agent)->toBe('my-agent');
    });

    it('excludes agent from toArray when null', function () {
        $config = new ResumeSessionConfig;

        expect($config->toArray())->not->toHaveKey('agent');
    });

    it('accepts commands parameter', function () {
        $handler = fn () => null;
        $commands = [
            ['name' => 'deploy', 'handler' => $handler, 'description' => 'Deploy the app'],
        ];

        $config = new ResumeSessionConfig(commands: $commands);

        expect($config->commands)->toBe($commands);
    });

    it('includes commands in toArray', function () {
        $handler = fn () => null;
        $config = new ResumeSessionConfig(commands: [
            ['name' => 'deploy', 'handler' => $handler],
        ]);

        expect($config->toArray()['commands'])->toHaveCount(1)
            ->and($config->toArray()['commands'][0]['name'])->toBe('deploy');
    });

    it('can be created from array with commands', function () {
        $handler = fn () => null;
        $config = ResumeSessionConfig::fromArray([
            'commands' => [
                ['name' => 'test', 'handler' => $handler],
            ],
        ]);

        expect($config->commands)->toHaveCount(1)
            ->and($config->commands[0]['name'])->toBe('test');
    });

    it('excludes commands from toArray when null', function () {
        $config = new ResumeSessionConfig;

        expect($config->toArray())->not->toHaveKey('commands');
    });
});
