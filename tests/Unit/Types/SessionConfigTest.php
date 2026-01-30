<?php

declare(strict_types=1);

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
        ]);

        expect($config->sessionId)->toBe('test-session-id')
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
            ->and($config->infiniteSessions->backgroundCompactionThreshold)->toBe(0.80);
    });

    it('can be created from array with minimal fields', function () {
        $config = SessionConfig::fromArray([]);

        expect($config->sessionId)->toBeNull()
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
            ->and($config->infiniteSessions)->toBeNull();
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
        );

        $array = $config->toArray();

        expect($array['sessionId'])->toBe('session-123')
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
            ->and($array['infiniteSessions'])->toBe(['enabled' => false]);
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

        expect($config)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
