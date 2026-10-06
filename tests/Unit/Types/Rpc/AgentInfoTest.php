<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\AgentInfo;
use Revolution\Copilot\Enums\AgentInfoSource;
use Revolution\Copilot\Enums\AgentModelPolicy;

describe('AgentInfo', function () {
    it('can be created with all fields', function () {
        $info = new AgentInfo(
            name: 'my-agent',
            displayName: 'My Agent',
            description: 'Does things',
            path: '/path/to/agent.md',
            prompt: 'You are an agent',
            userInvocable: false,
            disableModelInvocation: true,
        );

        expect($info->name)->toBe('my-agent')
            ->and($info->displayName)->toBe('My Agent')
            ->and($info->description)->toBe('Does things')
            ->and($info->path)->toBe('/path/to/agent.md')
            ->and($info->prompt)->toBe('You are an agent')
            ->and($info->userInvocable)->toBeFalse()
            ->and($info->disableModelInvocation)->toBeTrue();
    });

    it('handles default values', function () {
        $info = new AgentInfo(name: 'my-agent', displayName: 'My Agent', description: 'Does things');

        expect($info->path)->toBeNull()
            ->and($info->prompt)->toBeNull()
            ->and($info->userInvocable)->toBeNull()
            ->and($info->disableModelInvocation)->toBeNull();
    });

    it('can be created from array', function () {
        $info = AgentInfo::fromArray([
            'name' => 'my-agent',
            'displayName' => 'My Agent',
            'description' => 'Does things',
            'disableModelInvocation' => true,
        ]);

        expect($info->disableModelInvocation)->toBeTrue();
    });

    it('serializes to array omitting null values', function () {
        $info = new AgentInfo(name: 'my-agent', displayName: 'My Agent', description: 'Does things');

        expect($info->toArray())->toBe([
            'name' => 'my-agent',
            'displayName' => 'My Agent',
            'description' => 'Does things',
        ]);
    });

    it('includes disableModelInvocation when set', function () {
        $info = new AgentInfo(
            name: 'my-agent',
            displayName: 'My Agent',
            description: 'Does things',
            disableModelInvocation: false,
        );

        expect($info->toArray())->toBe([
            'name' => 'my-agent',
            'displayName' => 'My Agent',
            'description' => 'Does things',
            'disableModelInvocation' => false,
        ]);
    });

    it('preserves built-in source and model-selection metadata', function () {
        $info = AgentInfo::fromArray([
            'description' => 'Reviews code',
            'displayName' => 'Reviewer',
            'id' => 'builtin:reviewer',
            'model' => 'gpt-review',
            'modelPolicy' => 'required',
            'models' => ['gpt-review', 'claude-review'],
            'mcpServers' => ['github' => ['type' => 'http']],
            'name' => 'reviewer',
            'reasoningEffort' => 'high',
            'skills' => ['review-guidelines'],
            'source' => 'builtin',
            'tools' => ['read_file', 'search'],
        ]);

        expect($info->id)->toBe('builtin:reviewer')
            ->and($info->modelPolicy)->toBe(AgentModelPolicy::REQUIRED)
            ->and($info->source)->toBe(AgentInfoSource::BUILTIN)
            ->and($info->models)->toBe(['gpt-review', 'claude-review'])
            ->and($info->toArray())->toMatchArray([
                'id' => 'builtin:reviewer',
                'model' => 'gpt-review',
                'modelPolicy' => 'required',
                'models' => ['gpt-review', 'claude-review'],
                'mcpServers' => ['github' => ['type' => 'http']],
                'reasoningEffort' => 'high',
                'skills' => ['review-guidelines'],
                'source' => 'builtin',
                'tools' => ['read_file', 'search'],
            ]);
    });
});
