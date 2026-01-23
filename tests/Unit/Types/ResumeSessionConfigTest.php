<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ProviderConfig;
use Revolution\Copilot\Types\ResumeSessionConfig;

describe('ResumeSessionConfig', function () {
    it('can be created from array with all fields', function () {
        $handler = fn () => true;

        $config = ResumeSessionConfig::fromArray([
            'tools' => [['name' => 'test_tool']],
            'provider' => ['baseUrl' => 'https://api.example.com'],
            'onPermissionRequest' => $handler,
            'streaming' => true,
            'mcpServers' => ['server1' => ['command' => 'npx']],
            'customAgents' => [['name' => 'agent1']],
            'skillDirectories' => ['/path/to/skills'],
            'disabledSkills' => ['skill1'],
        ]);

        expect($config->tools)->toBe([['name' => 'test_tool']])
            ->and($config->provider)->toBeInstanceOf(ProviderConfig::class)
            ->and($config->onPermissionRequest)->toBe($handler)
            ->and($config->streaming)->toBeTrue()
            ->and($config->mcpServers)->toBe(['server1' => ['command' => 'npx']])
            ->and($config->customAgents)->toBe([['name' => 'agent1']])
            ->and($config->skillDirectories)->toBe(['/path/to/skills'])
            ->and($config->disabledSkills)->toBe(['skill1']);
    });

    it('can be created from array with minimal fields', function () {
        $config = ResumeSessionConfig::fromArray([]);

        expect($config->tools)->toBeNull()
            ->and($config->provider)->toBeNull()
            ->and($config->onPermissionRequest)->toBeNull()
            ->and($config->streaming)->toBeNull()
            ->and($config->mcpServers)->toBeNull()
            ->and($config->customAgents)->toBeNull()
            ->and($config->skillDirectories)->toBeNull()
            ->and($config->disabledSkills)->toBeNull();
    });

    it('preserves ProviderConfig instance when passed directly', function () {
        $provider = new ProviderConfig(baseUrl: 'https://api.test.com');
        $config = ResumeSessionConfig::fromArray([
            'provider' => $provider,
        ]);

        expect($config->provider)->toBe($provider);
    });

    it('can convert to array with all fields', function () {
        $handler = fn () => true;

        $config = new ResumeSessionConfig(
            tools: [['name' => 'tool1']],
            provider: new ProviderConfig(baseUrl: 'https://api.test.com'),
            onPermissionRequest: $handler,
            streaming: true,
            mcpServers: ['server1' => ['command' => 'test']],
            customAgents: [['name' => 'agent1']],
            skillDirectories: ['/skills'],
            disabledSkills: ['skill1'],
        );

        $array = $config->toArray();

        expect($array['tools'])->toBe([['name' => 'tool1']])
            ->and($array['provider'])->toBe(['baseUrl' => 'https://api.test.com'])
            ->and($array['onPermissionRequest'])->toBe($handler)
            ->and($array['streaming'])->toBeTrue()
            ->and($array['mcpServers'])->toBe(['server1' => ['command' => 'test']])
            ->and($array['customAgents'])->toBe([['name' => 'agent1']])
            ->and($array['skillDirectories'])->toBe(['/skills'])
            ->and($array['disabledSkills'])->toBe(['skill1']);
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

        expect($config)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
