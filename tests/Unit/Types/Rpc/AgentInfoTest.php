<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\AgentInfo;

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
});
