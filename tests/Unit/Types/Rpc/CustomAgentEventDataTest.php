<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\CustomAgentEventData;

describe('CustomAgentEventData', function () {
    it('can be created from array with all fields', function () {
        $data = CustomAgentEventData::fromArray([
            'id' => 'agent-123',
            'name' => 'my-agent',
            'displayName' => 'My Agent',
            'description' => 'A helpful agent',
            'source' => 'project',
            'tools' => ['bash', 'edit', 'view'],
            'userInvocable' => true,
            'model' => 'gpt-5',
        ]);

        expect($data->id)->toBe('agent-123')
            ->and($data->name)->toBe('my-agent')
            ->and($data->displayName)->toBe('My Agent')
            ->and($data->description)->toBe('A helpful agent')
            ->and($data->source)->toBe('project')
            ->and($data->tools)->toBe(['bash', 'edit', 'view'])
            ->and($data->userInvocable)->toBeTrue()
            ->and($data->model)->toBe('gpt-5');
    });

    it('handles default values', function () {
        $data = CustomAgentEventData::fromArray([]);

        expect($data->id)->toBe('')
            ->and($data->name)->toBe('')
            ->and($data->displayName)->toBe('')
            ->and($data->description)->toBe('')
            ->and($data->source)->toBe('')
            ->and($data->tools)->toBe([])
            ->and($data->userInvocable)->toBeFalse()
            ->and($data->model)->toBeNull();
    });

    it('converts to array', function () {
        $data = CustomAgentEventData::fromArray([
            'id' => 'agent-1',
            'name' => 'test',
            'displayName' => 'Test Agent',
            'description' => 'Testing',
            'source' => 'user',
            'tools' => ['grep'],
            'userInvocable' => false,
        ]);

        expect($data->toArray())
            ->toHaveKey('id', 'agent-1')
            ->toHaveKey('name', 'test')
            ->toHaveKey('displayName', 'Test Agent')
            ->toHaveKey('description', 'Testing')
            ->toHaveKey('source', 'user')
            ->toHaveKey('tools', ['grep'])
            ->toHaveKey('userInvocable', false)
            ->not->toHaveKey('model');
    });

    it('includes model in toArray when set', function () {
        $data = new CustomAgentEventData(
            id: 'agent-1',
            name: 'test',
            displayName: 'Test',
            description: 'Testing',
            source: 'project',
            tools: [],
            userInvocable: true,
            model: 'claude-sonnet-4',
        );

        expect($data->toArray())->toHaveKey('model', 'claude-sonnet-4');
    });

    it('implements Arrayable interface', function () {
        $data = new CustomAgentEventData(
            id: 'a',
            name: 'b',
            displayName: 'c',
            description: 'd',
            source: 'e',
            tools: [],
            userInvocable: false,
        );

        expect($data)->toBeInstanceOf(Arrayable::class);
    });

    it('roundtrips through fromArray/toArray', function () {
        $original = [
            'id' => 'agent-roundtrip',
            'name' => 'roundtrip',
            'displayName' => 'Roundtrip Agent',
            'description' => 'Tests roundtrip',
            'source' => 'inherited',
            'tools' => ['bash', 'view'],
            'userInvocable' => true,
            'model' => 'gpt-5',
        ];

        $data = CustomAgentEventData::fromArray($original);
        expect($data->toArray())->toBe($original);
    });
});
